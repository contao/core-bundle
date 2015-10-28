<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Exception\AjaxRedirectResponseException;
use Contao\CoreBundle\Exception\InvalidRequestTokenException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Validates Contao request tokens.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class RequestTokenListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $routeNames;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var string
     */
    private $csrfTokenName;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface  $framework
     * @param array                     $routeNames
     * @param RouterInterface           $router
     * @param CsrfTokenManagerInterface $tokenManager
     * @param string                    $csrfTokenName
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        array $routeNames,
        RouterInterface $router,
        CsrfTokenManagerInterface $tokenManager,
        $csrfTokenName
    ) {
        $this->framework = $framework;
        $this->routeNames = $routeNames;
        $this->router = $router;
        $this->tokenManager = $tokenManager;
        $this->csrfTokenName = $csrfTokenName;
    }

    /**
     * Handle the request token.
     *
     * @param GetResponseEvent $event
     *
     * @throws AjaxRedirectResponseException|InvalidRequestTokenException If the token is invalid
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->framework->isInitialized()) {
            return;
        }

        $request = $event->getRequest();

        if (null === $request
            || 'POST' !== $request->getRealMethod()
            || !$request->attributes->has('_route')
            || !in_array($request->attributes->get('_route'), $this->routeNames)
        ) {
            return;
        }

        $this->setConstant();

        $token = new CsrfToken($this->csrfTokenName, $request->request->get('REQUEST_TOKEN'));

        if ($this->tokenManager->isTokenValid($token)) {
            return;
        }

        if ($request->isXmlHttpRequest()) {
            // @todo is contao_backend really the right solution here?
            throw new AjaxRedirectResponseException($this->router->generate('contao_backend'));
        }

        throw new InvalidRequestTokenException('Invalid request token. Please reload the page and try again.');
    }

    /**
     * Sets the REQUEST_TOKEN constant
     */
    private function setConstant()
    {
        // Deprecated since Contao 4.0, to be removed in Contao 5.0
        if (!defined('REQUEST_TOKEN')) {
            define('REQUEST_TOKEN', $this->tokenManager->getToken($this->csrfTokenName)->getValue());
        }
    }
}