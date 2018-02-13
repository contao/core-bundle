<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @contribute Johannes Pichler <j.pichler@webpixels.at>
 *
 * @description This Listener handles the new SessionListener handling from Symfony, what results into a bug
 *              in Contao, because the creation of the Cache Files is Broken.
 *
 *              The listener is registered with a priority of -2038, results in beeing executed just before the
 *              "Terminal42\HeaderReplay\EventListener\HeaderReplayListener::onKernelRequest()" listener
 *              with index -2048
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Controller\FrontendController;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class FinalCacheHeadersListener
{

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    private $container;


    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework, ContainerInterface $container)
    {
        $this->framework = $framework;
        $this->container = $container;


    }

    /**
     * Placeholder in case we need the request object as well in the future
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // do nothing, just a placeholder
    }

    /**
     * Adds the Contao headers to the Symfony response again after Session destroyed it.
     *
     * @description added new onKernelResponseListener, to fix issue 1246 with broken cache according to
     *              symfony/symfony 3.4.4
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        // added new onKernelResponseListener, to fix issue 1246

        if (!$this->framework->isInitialized()) {
            return;
        }


        $request = $event->getRequest();
        $response = $event->getResponse();

        $controllerAttribute = $request->get("_controller");
        $insertTagAttribute = $request->get("insertTag");
        $formatAttribute = $request->get("_format");


        // we distinguish between rendering of insert tags (sub requests) and page rendering (master request)
        if (


            // AND checks, if request is for an insert tag
            $controllerAttribute == "contao.controller.insert_tags:renderAction"

            // AND checks, if an insert tag attribute is given in the request
            && $insertTagAttribute

        ) {
            // FIST CASE


            // check for format html, because we only want to cache html content
            if ($formatAttribute !== "html") {
                return;
            }

            // a request for rendering insert tags
            if (
                // checks, inserttag should not be cached based on uncached pipe
                strpos($insertTagAttribute, "|uncached")

                // OR checks, inserttag should not be cached based on refrech pipe
                || strpos($insertTagAttribute, "|refresh")
            ) {
                $response->setPrivate();
            } else {

                $this->setCacheHeaders($request, $response);

            }


        } else if (
            // SECOND CASE


            // checks, if request is a master request
            $event->isMasterRequest()

            // AND if the controller ist the FrontendController with its static index action
            // what should be "Contao\CoreBundle\Controller\FrontendController::indexAction"
            && $controllerAttribute == FrontendController::class . "::indexAction"
        ) {
            // second case, a master request for rendering a page
            $this->setCacheHeaders($request, $response);

        }


    }

    /**
     * Set the cache headers according to the page settings.
     *
     * @param Request  $request  The request object
     * @param Response $response The response object
     * @return Response The response object
     */
    private function setCacheHeaders(Request $request, Response $response)
    {
        /** @var $objPage \PageModel */
        global $objPage;

        if (!$objPage) {
            return $response;
        }

        if (($objPage->cache === false || $objPage->cache === 0) && ($objPage->clientCache === false || $objPage->clientCache === 0)) {
            return $response->setPrivate();
        }

        // Do not cache the response if a user is logged in or the page is protected
        // TODO: Add support for proxies so they can vary on member context
        if (FE_USER_LOGGED_IN === true || BE_USER_LOGGED_IN === true || $objPage->protected || $this->hasAuthenticatedBackendUser($request)) {
            return $response->setPrivate();
        }

        if ($objPage->clientCache > 0) {
            $response->setMaxAge($objPage->clientCache);
        }

        if ($objPage->cache > 0) {
            $response->setSharedMaxAge($objPage->cache);
        }


        $response->isNotModified($request);

        return $response;
    }

    /**
     * Checks if there is an authenticated back end user.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function hasAuthenticatedBackendUser(Request $request)
    {
        if (!$request->cookies->has('BE_USER_AUTH')) {
            return false;
        }

        $sessionHash = $this->getSessionHash('BE_USER_AUTH');

        return $request->cookies->get('BE_USER_AUTH') === $sessionHash;
    }

    /**
     * Return the session hash
     *
     * @param string $strCookie The cookie name
     *
     * @return string The session hash
     */
    public function getSessionHash($strCookie)
    {
        $container = $this->container;
        $strHash = $container->get('session')->getId();

        if (!$container->getParameter('contao.security.disable_ip_check')) {
            $strHash .= \Environment::get('ip');
        }

        $strHash .= $strCookie;

        return sha1($strHash);
    }
}