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

        $ini = $this->parseIniFile($file);

        $options['load-after'] = $this->getLoadAfter($ini);

        return $options;
    }

    /**
     * Parses the file and returns the configuration array
     *
     * @param \SplFileInfo $file The file object
     *
     * @return array The configuration array
     *
     * @throws \RuntimeException If the file cannot be decoded
     */
    protected function parseIniFile(\SplFileInfo $file)
    {
        $ini = parse_ini_file($file, true);

        if (false === $ini) {
            throw new \RuntimeException("File $file cannot be decoded");
        }

        return $ini;
    }

    /**
     * Creates the load-after array from the ini configuration
     *
     * @param array $ini The autoload.ini array
     *
     * @return array The load-after array
     */
    protected function getLoadAfter($ini)
    {
        if (!isset($ini['requires']) || !is_array($ini['requires'])) {
            return [];
        }

        $requires = $ini['requires'];

        // Convert optional requirements
        foreach ($requires as &$v) {
            if (0 === strncmp($v, '*', 1)) {
                $v = substr($v, 1);
            }
        }

        return $requires;
    }
}
