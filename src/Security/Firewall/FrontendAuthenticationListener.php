<?php

namespace Contao\CoreBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class FrontendAuthenticationListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $providerKey;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        $providerKey = ''
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // skip non POST requests
        if (Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $formSubmit = $request->get('FORM_SUBMIT');
        $username = $request->get('username');
        $password = $request->get('password');

        // skip if there is no login form
        if (null === $formSubmit || !preg_match('/tl_login_\d+/', $formSubmit)) {
            return;
        }

        $token = new UsernamePasswordToken(
            $username,
            $password,
            $this->providerKey
        );

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
//            $class = get_class($failed);
//            throw new $class();
            // ... you might log something here

            $token = $this->tokenStorage->getToken();

            if ($token instanceof UsernamePasswordToken && $this->providerKey === $token->getProviderKey()) {
                $this->tokenStorage->setToken(null);
            }

            return;
        }
    }

}

