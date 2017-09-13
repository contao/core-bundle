<?php

namespace Contao\CoreBundle\HttpKernel\Header;

/**
 * Handles HTTP headers in PHP's native methods.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
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
    public function add($header, bool $replace = false)
    {
        header($header, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if ('cli' !== PHP_SAPI && !headers_sent()) {
            header_remove();
        }
    }
}
