<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\Authentication\ContaoToken;
use Contao\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

@trigger_error('Using the ContaoAuthenticator has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

/**
 * Class ContaoAuthenticator.
 *
 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
 */
class ContaoAuthenticator implements ContainerAwareInterface, SimplePreAuthenticatorInterface
{
    use ContainerAwareTrait;

    /**
     * @var ScopeMatcher
     */
    protected $scopeMatcher;

    /**
     * @param ScopeMatcher $scopeMatcher
     *
     * @deprecated Using the ContaoAuthenticator has been deprecated and will no longer work in Contao 5.0.
     */
    public function __construct(ScopeMatcher $scopeMatcher)
    {
        @trigger_error('Using the ContaoAuthenticator has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * Creates an authentication token.
     *
     * @param Request $request
     * @param string  $providerKey
     *
     * @return AnonymousToken
     *
     * @deprecated Using ContaoAuthenticator::createToken has been deprecated and will no longer work in Contao 5.0.
     */
    public function createToken(Request $request, $providerKey): AnonymousToken
    {
        @trigger_error('Using ContaoAuthenticator::createToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        return new AnonymousToken($providerKey, 'anon.');
    }

    /**
     * Authenticates a token.
     *
     * @param TokenInterface        $token
     * @param UserProviderInterface $userProvider
     * @param string                $providerKey
     *
     * @throws AuthenticationException
     *
     * @return TokenInterface|ContaoToken|AnonymousToken
     *
     * @deprecated Using ContaoAuthenticator::authenticateToken has been deprecated and will no longer work in Contao 5.0.
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface
    {
        @trigger_error('Using ContaoAuthenticator::authenticateToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        if ($this->canSkipAuthentication($token)) {
            return $token;
        }

        if (!$token instanceof AnonymousToken) {
            throw new AuthenticationException('The ContaoAuthenticator can only handle AnonymousToken.');
        }

        try {
            $user = $userProvider->loadUserByUsername($token->getSecret());

            if ($user instanceof User) {
                return new ContaoToken($user);
            }
        } catch (UsernameNotFoundException $e) {
            // ignore and return the original token
        }

        return $token;
    }

    /**
     * Checks if the token is supported.
     *
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return bool
     *
     * @deprecated Using ContaoAuthenticator::supportsToken has been deprecated and will no longer work in Contao 5.0.
     */
    public function supportsToken(TokenInterface $token, $providerKey): bool
    {
        @trigger_error('Using ContaoAuthenticator::supportsToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        return $token instanceof ContaoToken || $token instanceof AnonymousToken;
    }

    /**
     * Checks if the authentication can be skipped.
     *
     * @param TokenInterface $token
     *
     * @throws \LogicException
     *
     * @return bool
     *
     * @deprecated Using ContaoAuthenticator::canSkipAuthentication has been deprecated and will no longer work in Contao 5.0.
     */
    private function canSkipAuthentication(TokenInterface $token): bool
    {
        @trigger_error('Using ContaoAuthenticator::canSkipAuthentication has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        if ($token instanceof ContaoToken) {
            return true;
        }

        if (null === $this->container) {
            throw new \LogicException('The service container has not been set.');
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        return null === $request || !$this->scopeMatcher->isContaoRequest($request);
    }
}
