<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Autoload;

/**
 * Converts an INI configuration file into a configuration array
 *
 * @author Leo Feyer <https://contao.org>
 */
class IniParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(\SplFileInfo $file)
    {
        return [
            'bundles' => [$this->doParse($file)]
        ];
    }

    /**
     * Parses the file and returns the options array
     *
     * @param \SplFileInfo $file The file object
     *
     * @return array The configuration array
     *
     * @throws \RuntimeException If the file cannot be decoded
     */
    protected function doParse(\SplFileInfo $file)
    {
        $options = [
            'class' => null,
            'name'  => basename(dirname(dirname($file)))
        ];

        // The autoload.ini file is optional
        if (!$file->isFile()) {
            return $options;
        }

        $ini = parse_ini_file($file, true);

        if (false === $ini) {
            $error = error_get_last();

            throw new \RuntimeException("File $file cannot be decoded: " . $error['message']);
        }

        // The requires are optional, too
        if (!isset($ini['requires']) || !is_array($ini['requires'])) {
            return $options;
        }

        $options['load-after'] = $ini['requires'];

        // Convert optional requirements
        foreach ($options['load-after'] as $k => $v) {
            if (0 === strncmp($v, '*', 1)) {
                $options['load-after'][$k] = substr($v, 1);
            }
        }

        return $options;
    }
}
