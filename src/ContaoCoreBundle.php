<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle;

use Contao\CoreBundle\DependencyInjection\Compiler\AddImagineClassPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddResourcesPathsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddSessionBagsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\DoctrineMigrationsPass;
use Contao\CoreBundle\DependencyInjection\ContaoCoreExtension;
use Contao\CoreBundle\Doctrine\DBAL\Types\BinaryStringType;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Configures the Contao core bundle.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ContaoCoreBundle extends Bundle
{
    const SCOPE_BACKEND = 'backend';
    const SCOPE_FRONTEND = 'frontend';

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ContaoCoreExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!Type::hasType(BinaryStringType::NAME)) {
            Type::addType(BinaryStringType::NAME, BinaryStringType::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if (!Type::hasType(BinaryStringType::NAME)) {
            Type::addType(BinaryStringType::NAME, BinaryStringType::class);
        }

        $container->addCompilerPass(
            new AddPackagesPass($container->getParameter('kernel.root_dir').'/../vendor/composer/installed.json')
        );

        $container->addCompilerPass(new AddSessionBagsPass());
        $container->addCompilerPass(new AddResourcesPathsPass());
        $container->addCompilerPass(new AddImagineClassPass());
        $container->addCompilerPass(new DoctrineMigrationsPass());
    }
}
