<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
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
     * {@inheritdoc}
     */
    public function getAssetsPath()
    {
        return $this->getPath() . '/../legacy/assets';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath()
    {
        return $this->getPath() . '/../legacy/config';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaPath()
    {
        return $this->getPath() . '/../legacy/dca';
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguagesPath()
    {
        return $this->getPath() . '/../legacy/languages';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath()
    {
        return $this->getPath() . '/../legacy/templates';
    }
}
