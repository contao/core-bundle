<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Util\LocaleUtil;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * The priority must be lower than the one of the firewall listener (defaults to 8).
 *
 * @internal
 */
#[AsEventListener(priority: 7)]
class BackendLocaleListener
{
    public function __construct(
        private readonly Security $security,
        private readonly LocaleAwareInterface $translator,
    ) {
    }

    /**
     * Sets the default locale based on the user language.
     */
    public function __invoke(RequestEvent $event): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->language) {
            return;
        }

        $request = $event->getRequest();
        $request->setLocale($user->language);

        $this->translator->setLocale($user->language);

        // Deprecated since Contao 4.0, to be removed in Contao 6.0
        $GLOBALS['TL_LANGUAGE'] = LocaleUtil::formatAsLanguageTag($user->language);
    }
}
