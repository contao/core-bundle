<?php

namespace Contao\CoreBundle\Asset;

use Symfony\Component\Asset\Context\ContextInterface;

class ContaoContext implements ContextInterface
{
    /**
     * @var string
     */
    private $constant;

    /**
     * Constructor.
     *
     * @param string $constant
     */
    public function __construct(string $constant)
    {
        $this->constant = $constant;
    }

    /**
     * Gets the base path.
     *
     * @return string The base path
     */
    public function getBasePath()
    {
        if (!defined($this->constant)) {
            return '';
        }

        return rtrim(constant($this->constant), '/');
    }

    /**
     * Checks whether the request is secure or not.
     *
     * @return bool true if the request is secure, false otherwise
     */
    public function isSecure()
    {
        return defined($this->constant) && 0 === strpos(constant($this->constant), 'https://');
    }
}
