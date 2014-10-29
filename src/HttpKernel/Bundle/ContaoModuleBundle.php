<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\HttpKernel\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Configures a Contao module bundle
 *
 * @author Leo Feyer <https://contao.org>
 */
class ContaoModuleBundle extends Bundle implements ContaoBundleInterface
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * Set the name
     *
     * @param string $name    The module name
     * @param string $rootDir The application root directory
     */
    public function __construct($name, $rootDir)
    {
        $this->name    = $name;
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getContaoResourcesPath()
    {
        return $this->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if (null === $this->path) {
            $this->path = dirname($this->rootDir) . '/system/modules/' . $this->name;
        }

        return $this->path;
    }
}
