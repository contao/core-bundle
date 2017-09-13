<?php

namespace Contao\CoreBundle\HttpKernel\Header;

/**
 * Handles HTTP headers in memory (for unit tests).
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class MemoryHeaderStorage implements HeaderStorageInterface
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * Constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function add($header, bool $replace = false)
    {
        $this->headers[] = $header;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->headers = [];
    }
}
