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

use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Idna;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * UserChecker checks the user account flags.
 */
class UserChecker implements UserCheckerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var Session */
    protected $session;

    /** @var ScopeMatcher */
    protected $scopeMatcher;

    /** @var RequestStack */
    protected $requestStack;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator, \Swift_Mailer $mailer, Session $session, ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->scopeMatcher = $scopeMatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $time = time();
        $request = $this->requestStack->getCurrentRequest();

        // Lock the account if there are too many login attempts
        if ($user->loginCount < 1) {
            $user->locked = $time;
            $user->loginCount = (int) Config::get('loginCount');
            $user->save();

            $this->session->getFlashBag()->set(
                'contao.BE.error',
                $this->translator->trans(
                    'ERR.accountLocked',
                    [ceil((((int) $user->locked + (int) Config::get('lockPeriod')) - $time) / 60)],
                'contao_default'
                )
            );

            $this->logger->info(
                vsprintf(
                    'User %s has been locked for %s minutes',
                    [
                        $user->getUsername(),
                        ceil((int) Config::get('lockPeriod') / 60),
                    ]
                ),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            // Send admin notification
            if (Config::get('adminEmail')) {
                $email = new \Swift_Message();
                $email->setTo(Config::get('adminEmail'));
                $email->setSubject(
                    $this->translator->trans(
                        'MSC.lockedAccount.0',
                        [],
                        'contao_default'
                    )
                );
                $email->setBody(
                    $this->translator->trans(
                        'MSC.lockedAccount.1',
                        [
                            $user->getUsername(),
                            ($this->scopeMatcher->isFrontendRequest($request) ? $user->firstname.' '.$user->lastname : $user->name),
                            Idna::decode(Environment::get('base')),
                            ceil(((int) Config::get('lockPeriod')) / 60),
                        ],
                        'contao_default'
                    ),
                    'text/plain'
                );

                $this->mailer->send($email);
            }

            throw new LockedException();
        }

        // Check whether the account is locked
        if (false === $user->isAccountNonLocked()) {
            $this->session->getFlashBag()->set(
                'contao.BE.error',
                $this->translator->trans(
                    'ERR.accountLocked',
                    [ceil((($user->locked + (int) Config::get('lockPeriod')) - $time) / 60)],
                    'contao_default'
                )
            );

            throw new LockedException();
        }

        // Check whether the account is disabled
        if (false === $user->isEnabled()) {
            $this->session->getFlashBag()->set(
                'contao.BE.error',
                $this->translator->trans(
                    'ERR.invalidLogin',
                    [],
                    'contao_default'
                )
            );

            $this->logger->info(
                'The account has been disabled',
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            throw new DisabledException();
        }

        // Check wether login is allowed (front end only)
        if ($user instanceof FrontendUser && false === $user->login) {
            $this->session->getFlashBag()->set(
                'contao.BE.error',
                $this->translator->trans(
                    'ERR.invalidLogin',
                    [],
                    'contao_default'
                )
            );

            $this->logger->info(
                vsprintf(
                    'User %s is not allowed to log in',
                    [$user->getUsername()]
                ),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            throw new DisabledException();
        }

        $start = (int) $user->start;
        $stop = (int) $user->stop;

        // Check whether account is not active yet or anymore
        if ($start || $stop) {
            $time = Date::floorToMinute($time);

            if ($start && $start > $time) {
                $this->session->getFlashBag()->set(
                    'contao.BE.error',
                    $this->translator->trans(
                        'ERR.invalidLogin',
                        [],
                        'contao_default'
                    )
                );

                $this->logger->info(
                    vsprintf(
                        'The account was not active yet (activation date: %s)',
                        [Date::parse(Config::get('dateFormat'), $start)]
                    ),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
                );

                throw new DisabledException();
            }

            if ($stop && $stop <= ($time + 60)) {
                $this->session->getFlashBag()->set(
                    'contao.BE.error',
                    $this->translator->trans(
                        'ERR.invalidLogin',
                        [],
                        'contao_default'
                    )
                );

                $this->logger->info(
                    vsprintf(
                        'The account was not active anymore (deactivation date: %s)',
                        [Date::parse(Config::get('dateFormat'), $stop)]
                    ),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
                );

                throw new DisabledException();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
