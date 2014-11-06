<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle;

use Contao\CoreBundle\DependencyInjection\Compiler\AddRouteProvidersPass;
use Contao\System;
use Contao\CoreBundle\HttpKernel\Bundle\ContaoBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures the bundle
 *
 * @author Leo Feyer <https://contao.org>
 */
class ContaoCoreBundle extends ContaoBundle
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

        $container->addCompilerPass(new AddRouteProvidersPass());
    }
}
