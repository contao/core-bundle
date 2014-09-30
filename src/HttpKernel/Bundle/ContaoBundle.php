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
 * Configures a Contao bundle
 *
 * @author Leo Feyer <https://contao.org>
 */
class ContaoBundle extends Bundle implements ContaoBundleInterface
{
    /**
     * @var string
     */
    protected $relpath;

    /**
     * {@inheritdoc}
     */
    public function getRelativePath()
    {
        if (null === $this->relpath) {
            $this->relpath = str_replace(
                realpath(__DIR__ . '/../../../../../../') . '/', '', $this->getPath()
            );
        }

        return $this->relpath;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsPath()
    {
        return $this->getRelativePath() . '/Resources/assets';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath()
    {
        return $this->getRelativePath() . '/Resources/config';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaPath()
    {
        return $this->getRelativePath() . '/Resources/dca';
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguagesPath()
    {
        return $this->getRelativePath() . '/Resources/languages';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath()
    {
        return $this->getRelativePath() . '/Resources/templates';
    }
}
