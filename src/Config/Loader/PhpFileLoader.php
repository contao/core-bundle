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
        $code = rtrim(file_get_contents($file));

        // Opening tag
        if (0 === strncmp($code, '<?php', 5)) {
            $code = substr($code, 5);
        }

        // Access check
        $code = str_replace(
            [
                " if (!defined('TL_ROOT')) die('You cannot access this file directly!');",
                " if (!defined('TL_ROOT')) die('You can not access this file directly!');",
            ],
            '',
            $code
        );

        // Closing tag
        if (substr($code, -2) === '?>') {
            $code = substr($code, 0, -2);
        }

        // declare() statements
        $lines = explode("\n", $code);
        $codeNew = '';
        $processed = false;

        foreach ($lines as $line) {
            if ('' === $line || $processed) {
                $codeNew .= $line . "\n";
                continue;
            }

            // Until now, every line was empty which means this is the first
            // one that's not so it must be the declare statement if it's there
            // as this one has to be the first statement right after <?php
            if (preg_match('/^declare\([^)]+\);$/', $line)) {
                $processed = true;
            } else {
                $codeNew .= $line . "\n";
                $processed = true;
            }
        }

        return rtrim($codeNew)."\n";
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
