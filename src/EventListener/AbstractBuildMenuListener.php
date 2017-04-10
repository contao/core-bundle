<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Abstract class for menu listeners.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class AbstractBuildMenuListener
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * Constructor.
     *
     * @param RouterInterface       $router
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack          $requestStack
     */
    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    /**
     * Returns the back end user object.
     *
     * @return BackendUser
     *
     * @throws \RuntimeException
     */
    protected function getUser()
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (null === $user) {
            throw new \RuntimeException('The token does not contain a user');
        }

        return $user;
    }

    /**
     * Returns a label.
     *
     * @param $key
     *
     * @return string
     */
    protected function getLabel($key)
    {
        if (isset($GLOBALS['TL_LANG']['MSC'][$key])) {
            return $GLOBALS['TL_LANG']['MSC'][$key];
        }

        return $key;
    }

    /**
     * Generates a Contao compatible route.
     *
     * @param string  $name
     * @param string  $do
     *
     * @param Request $request
     *
     * @return bool|string
     */
    protected function route($name, $do, Request $request)
    {
        $params = [
            'do' => $do,
            'target' => $request->query->get('target'),
            'value' => $request->query->get('value'),
            'popup' => $request->query->get('popup'),
            'switch' => $request->query->get('switch'),
        ];

        $url = $this->router->generate($name, $params);
        $url = substr($url, strlen($request->getBasePath()) + 1);

        return $url;
    }
}
