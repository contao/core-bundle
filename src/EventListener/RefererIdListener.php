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

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * The priority must be lower than the one of the Symfony route listener (defaults
 * to 32).
 *
 * @internal
 */
#[AsEventListener(priority: 20)]
class RefererIdListener
{
    private string|null $token = null;

    public function __construct(
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    /**
     * Adds the referer ID to the request.
     */
    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isBackendMainRequest($event)) {
            return;
        }

        $request = $event->getRequest();

        if (null === $this->token) {
            if ($request->isXmlHttpRequest() && $request->query->has('ref')) {
                $this->token = $request->query->get('ref');
            } else {
                $this->token = $this->tokenGenerator->generateToken();
            }
        }

        $request->attributes->set('_contao_referer_id', $this->token);
    }
}
