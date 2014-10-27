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
 * Converts a JSON configuration file into a configuration array
 *
 * @author Leo Feyer <https://contao.org>
 */
class JsonParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException If $file is not a file
     */
    public function parse(\SplFileInfo $file)
    {
        if (!$file->isFile()) {
            throw new \InvalidArgumentException("$file is not a file");
        }

        $json = $this->parseJsonFile($file);

        foreach ($json['bundles'] as $class => &$options) {
            $ref = new \ReflectionClass($class);

            $options['class'] = $class;
            $options['name']  = $ref->getShortName();
        }

        return $json;
    }

    /**
     * Parses the file and returns the configuration array
     *
     * @param \SplFileInfo $file The file object
     *
     * @return array The configuration array
     *
     * @throws \RuntimeException If the file cannot be decoded or there are no bundles
     */
    protected function parseJsonFile(\SplFileInfo $file)
    {
        $json = json_decode(file_get_contents($file), true);

        if (null === $json) {
            throw new \RuntimeException("File $file cannot be decoded: " . json_last_error_msg());
        }

        if (empty($json['bundles'])) {
            throw new \RuntimeException("No bundles defined in $file");
        }

        return $json;
    }
}
