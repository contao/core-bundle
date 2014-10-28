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
abstract class ContaoBundle extends Bundle implements ContaoBundleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigPath()
    {
        return $this->getPath() . '/Resources/config';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaPath()
    {
        return $this->getPath() . '/Resources/dca';
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguagesPath()
    {
        return $this->getPath() . '/Resources/languages';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath()
    {
        return $this->getPath() . '/Resources/templates';
    }
}
