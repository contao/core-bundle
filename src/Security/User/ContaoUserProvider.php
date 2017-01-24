<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\User;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Contao\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides a Contao front end or back end user object.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author David Greminger <https://github.com/bytehead>
 */
class ContaoUserProvider implements UserProviderInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param ScopeMatcher $scopeMatcher
     */
    public function __construct(ContaoFrameworkInterface $framework, ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->scopeMatcher = $scopeMatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     *
     * @return BackendUser|FrontendUser
     */
    public function loadUserByUsername($username)
    {
        $this->framework->initialize();

        if ($this->isBackendUsername($username)) {


            $user = BackendUser::getInstance();

            if (true === $user->findBy('username', $username)) {
                return BackendUser::getInstance();
            }

            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        if ($this->isFrontendUsername($username)) {
            $this->framework->initialize();

            return FrontendUser::getInstance();
        }

        throw new UsernameNotFoundException('Can only load user "frontend".');
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'Contao\User');
    }

    /**
     * Checks if the given username can be mapped to a front end user.
     *
     * @param string $username
     *
     * @return bool
     */
    private function isFrontendUsername($username)
    {
        return 'frontend' === $username && $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest());
    }

    /**
     * Checks if the given username can be mapped to a back end user.
     *
     * @param string $username
     *
     * @return bool
     */
    private function isBackendUsername($username)
    {
        return $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest());
    }
}
