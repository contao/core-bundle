<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Twig\Runtime;

use Contao\CoreBundle\Routing\ResponseContext\Csp\CspHandler;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Nelmio\SecurityBundle\Twig\CSPRuntime as NelmioCSPRuntime;
use Twig\Extension\RuntimeExtensionInterface;

final class CspRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly NelmioCSPRuntime|null $nelmioCSPRuntime = null,
    ) {
    }

    public function getNonce(string $directive): string|null
    {
        $responseContext = $this->responseContextAccessor->getResponseContext();

        if (!$responseContext?->has(CspHandler::class)) {
            // Forward to Nelmio's CSPRuntime method
            if ($this->nelmioCSPRuntime) {
                return $this->nelmioCSPRuntime->getCSPNonce(preg_replace('/^(script|style)-src/', '$1', $directive));
            }

            return '';
        }

        /** @var CspHandler $csp */
        $csp = $responseContext->get(CspHandler::class);

        return $csp->getNonce($directive);
    }

    public function addSource(string $directive, string $source): void
    {
        $responseContext = $this->responseContextAccessor->getResponseContext();

        if (!$responseContext?->has(CspHandler::class)) {
            return;
        }

        /** @var CspHandler $csp */
        $csp = $responseContext->get(CspHandler::class);
        $csp->addSource($directive, $source);
    }

    public function addHash(string $directive, string $source, string $algorithm = 'sha384'): void
    {
        $responseContext = $this->responseContextAccessor->getResponseContext();

        if (!$responseContext?->has(CspHandler::class)) {
            return;
        }

        /** @var CspHandler $csp */
        $csp = $responseContext->get(CspHandler::class);
        $csp->addHash($directive, $source, $algorithm);
    }
}