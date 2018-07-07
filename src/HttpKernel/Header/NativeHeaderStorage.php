<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\HttpKernel\Header;

/**
 * Handles HTTP headers in PHP's native methods.
 */
class NativeHeaderStorage implements HeaderStorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return headers_list();
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $header): void
    {
        header($header);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        if ('cli' !== \PHP_SAPI && !headers_sent()) {
            header_remove();
        }
    }
}
