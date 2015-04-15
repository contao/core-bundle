<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Session\Attribute\AttributeBagAdapter;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Stores the session data of a back end user so the state can be recreated
 * on the next login.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class StoreSessionListener extends ScopeAwareListener
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(
        SessionInterface $session,
        Connection $connection
    ) {
        $this->session = $session;
        $this->connection = $connection;
    }

    /**
     * Stores the referer data of a back end user so the state can be recreated
     * on the next login.
     *
     * @param FilterResponseEvent $event The event object
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->isBackendMasterRequest($event)) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->storeReferer($request)) {
            $this->storeSession();
            return;
        }

        $key = $this->getRefererKey($request);
        $session = $this->getSessionBag();

        // FIXME: Write this part of the code in beautiful instead of using
        // the adapter
        $session = new AttributeBagAdapter($session);

        if (!is_array($session[$key]) || !is_array($session[$key][TL_REFERER_ID])) {
            $session[$key][TL_REFERER_ID]['last'] = '';
        }

        while (count($session[$key]) >= 25) {
            array_shift($session[$key]);
        }

        $ref = \Input::get('ref');

        if ($ref != '' && isset($session[$key][$ref])) {
            if (!isset($session[$key][TL_REFERER_ID])) {
                $session[$key][TL_REFERER_ID] = array();
            }

            $session[$key][TL_REFERER_ID]         = array_merge($session[$key][TL_REFERER_ID], $session[$key][$ref]);
            $session[$key][TL_REFERER_ID]['last'] = $session[$key][$ref]['current'];
        } elseif (count($session[$key]) > 1) {
            $session[$key][TL_REFERER_ID] = end($session[$key]);
        }

        $session[$key][TL_REFERER_ID]['current'] = substr(\Environment::get('requestUri'), strlen(\Environment::get('path')) + 1);

        $this->storeSession();
    }

    /**
     * Check if we need to store the referer
     *
     * @param Request $request
     *
     * @return bool
     */
    private function storeReferer(Request $request)
    {
        if (!$request->query->has('act')
            && !$request->query->has('key')
            && !$request->query->has('token')
            && !$request->query->has('state')
            && 'feRedirect' !== $request->query->get('do')
            && !$request->isXmlHttpRequest()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Gets the referer key
     *
     * @param Request $request
     *
     * @return string
     */
    private function getRefererKey(Request $request)
    {
        return $request->query->has('popup') ? 'popupReferer' : 'referer';
    }

    /**
     * Gets the session bag
     *
     * @return AttributeBagInterface
     */
    private function getSessionBag()
    {
        return $this->session->getBag('contao_backend');
    }

    /**
     * Stores the session data in the database
     */
    private function storeSession()
    {
        // FIXME: Replace with security component
        $userId = BackendUser::getInstance()->id;

        $this->connection->prepare('UPDATE tl_user SET session=? WHERE id=?')
            ->execute([
                    serialize($this->getSessionBag()->all()),
                    $userId
                ]
            );
    }
}
