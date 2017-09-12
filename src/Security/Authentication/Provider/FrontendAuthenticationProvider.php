<?php

namespace Contao\CoreBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendAuthenticationProvider extends DaoAuthenticationProvider
{
    private $userProvider;
    private $encoderFactory;

    public function __construct(
        UserProviderInterface $userProvider,
        UserCheckerInterface $userChecker,
        $providerKey,
        EncoderFactoryInterface $encoderFactory,
        $hideUserNotFoundExceptions = true
    ) {
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;

        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken;
    }

    public function authenticate(TokenInterface $token)
    {
        return parent::authenticate($token);
    }
}
