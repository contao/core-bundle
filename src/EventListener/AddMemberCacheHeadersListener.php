<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Adds headers to the response that are related to the current member information.
 * These can be used to vary on so every member/ member group gets its own cache.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class AddMemberCacheHeadersListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $secret;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param TokenStorageInterface    $tokenStorage
     * @param string                   $secret
     */
    public function __construct(ContaoFrameworkInterface $framework, TokenStorageInterface $tokenStorage, $secret)
    {
        $this->framework = $framework;
        $this->tokenStorage = $tokenStorage;
        $this->secret = $secret;
    }

    /**
     * Adds the Contao headers to the Symfony response.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (null === ($token = $this->tokenStorage->getToken())) {
            return;
        }

        if (null === ($user = $token->getUser())) {
            return;
        }

        $this->framework->initialize();

        if (!$user instanceof FrontendUser) {
            return;
        }

        $response = $event->getResponse();

        // Add the member hash
        $response->headers->set('Contao-Member-Context-Hash',
            $this->generateHash($user->id)
        );
        // Add the member group hash
        $response->headers->set('Contao-MemberGroup-Context-Hash',
            $this->generateHash(implode(',', $user->getGroups()))
        );
    }

    /**
     * Generates a sha256 string based on a string and the application secret.
     *
     * @param $string
     *
     * @return string
     */
    private function generateHash($string)
    {
        return hash('sha256', $string . ':' . $this->secret);
    }
}
