<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Config\Loader;

use Symfony\Component\Config\Loader\Loader;

/**
 * Reads PHP files and returns the content without the opening and closing PHP tags.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PhpFileLoader extends Loader
{
    const NAMESPACED = 'namespaced';

    /**
     * Reads the contents of a PHP file stripping the opening and closing PHP tags.
     *
     * @param string      $file
     * @param string|null $type
     *
     * @return string
     */
    public function load($file, $type = null)
    {
        $code = '';
        $namespace = '';
        $stream = new \PHP_Token_Stream($file);

        foreach ($stream as $token) {
            switch (true) {
                case $token instanceof \PHP_Token_OPEN_TAG:
                case $token instanceof \PHP_Token_CLOSE_TAG:
                    // remove
                    break;

                case $token instanceof \PHP_Token_NAMESPACE:
                    if ('{' === $token->getName()) {
                        $namespace = false;
                        $code .= $token;
                    } else {
                        $namespace = $token->getName().' ';
                        $code .= '//'.$token;
                    }
                    break;

                case $token instanceof \PHP_Token_DECLARE:
                    $code .= '//'.$token;
                    break;

                default:
                    $code .= $token;
            }
        }

        if (false !== $namespace && self::NAMESPACED === $type) {
            return sprintf("\nnamespace %s{%s}\n", $namespace, $this->stripLegacyCheck($code));
        }

        return $this->stripLegacyCheck($code);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Strips the legacy check from the code.
     *
     * @param string $code
     *
     * @return string
     */
    private function stripLegacyCheck($code)
    {
        $code = str_replace(
            [
                "if (!defined('TL_ROOT')) die('You cannot access this file directly!');",
                "if (!defined('TL_ROOT')) die('You can not access this file directly!');",
            ],
            '',
            $code
        );

        return "\n".trim($code)."\n";
    }
}
