<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle;

use Contao\CoreBundle\DependencyInjection\Compiler\AddContaoRouteLoadersPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddRouteProvidersPass;
use Contao\CoreBundle\HttpKernel\Bundle\ContaoBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures the Contao core bundle.
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
        require_once __DIR__ . '/../contao/bootstrap.php';
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        require_once __DIR__ . '/../contao/bootstrap.php';

        $container->addCompilerPass(new AddContaoRouteLoadersPass());
        $container->addCompilerPass(new AddRouteProvidersPass());
    }
}
