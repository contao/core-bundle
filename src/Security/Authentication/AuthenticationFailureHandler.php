<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = [], LoggerInterface $logger = null)
    {
        $this->defaultOptions['failure_path_parameter'] = 'redirect';

        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    /**
     * Logs the security exception to the Contao back end.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @throws \RuntimeException
     *
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if (null !== $this->logger) {
            $user = $exception instanceof AccountStatusException ? $exception->getUser() : null;
            $username = $user instanceof UserInterface ? $user->getUsername() : $request->request->get('username');

            $this->logger->info(
                $exception->getMessage(),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS, $username)]
            );
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
