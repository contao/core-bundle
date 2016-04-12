<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Config\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads YAML files and returns the content.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class YamlDcaFileLoader extends Loader
{
    /**
     * Reads the contents of a YAML file.
     *
     * @param string      $file A YAML file path
     * @param string|null $type The resource type
     *
     * @return string The PHP code without the PHP tags
     */
    public function load($file, $type = null)
    {
        $table = basename($file, '.yml');
        $data = var_export(Yaml::parse(file_get_contents($file)), true);
        return "\$GLOBALS['TL_DCA']['$table'] = array_replace_recursive($data, \$GLOBALS['TL_DCA']['$table']);\n";
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
