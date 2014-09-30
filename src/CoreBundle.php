<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle;

use Contao\System;
use Contao\Bundle\CoreBundle\DependencyInjection\Compiler\AddContaoConfigurationPass;
use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures the bundle
 *
 * @author Leo Feyer <https://contao.org>
 */
class CoreBundle extends ContaoBundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        System::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        System::boot();

        $container->addCompilerPass(new AddContaoConfigurationPass());
    }
}
