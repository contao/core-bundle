<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\HttpKernel\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Configures a Contao legacy bundle
 *
 * @author Leo Feyer <https://contao.org>
 */
class ContaoLegacyBundle extends Bundle implements ContaoBundleInterface
{
    /**
     * @var string
     */
    protected $relpath;

    /**
     * Set the name
     *
     * @param string $name The module name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath()
    {
        if (null === $this->relpath) {
            $this->relpath = 'system/modules/' . $this->name;
        }

        return $this->relpath;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if (null === $this->path) {
            $this->path = realpath(__DIR__ . '/../../../../../../') . '/system/modules/' . $this->name;
        }

        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsPath()
    {
        return $this->getRelativePath() . '/assets';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath()
    {
        return $this->getRelativePath() . '/config';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaPath()
    {
        return $this->getRelativePath() . '/dca';
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguagesPath()
    {
        return $this->getRelativePath() . '/languages';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath()
    {
        return $this->getRelativePath() . '/templates';
    }
}
