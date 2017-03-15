<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class DoctrineSchemaPass implements CompilerPassInterface
{
    const DIFF_COMMAND_ID = 'console.command.contao_corebundle_command_doctrinemigrationsdiffcommand';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($this->hasOrm($container)) {
            $provider = $container->getDefinition('contao.doctrine.schema_provider');
            $provider->addArgument($container->getDefinition('doctrine.orm.entity_manager'));
        }

        if ($this->hasMigrationsBundle($container)) {
            $this->overrideMigrationsDiffCommand($container);
        }
    }

    /**
     * Checks if the Doctrine migrations bundle is enabled.
     *
     * @param ContainerBuilder $container
     *
     * @return bool
     */
    private function hasMigrationsBundle(ContainerBuilder $container)
    {
        return in_array(
            'Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle',
            $container->getParameter('kernel.bundles'),
            true
        );
    }

    /**
     * Checks if Doctrine ORM is enabled.
     *
     * @param ContainerBuilder $container
     *
     * @return bool
     */
    private function hasOrm(ContainerBuilder $container)
    {
        return $container->has('doctrine.orm.entity_manager');
    }

    /**
     * Registers the custom doctrine:schema:diff command that works without ORM.
     *
     * @param ContainerBuilder $container
     */
    private function overrideMigrationsDiffCommand(ContainerBuilder $container)
    {
        $provider = new Definition('Contao\CoreBundle\Doctrine\Schema\MigrationsSchemaProvider');
        $provider->addArgument($container->getDefinition('service_container'));

        $command = new Definition('Contao\CoreBundle\Command\DoctrineMigrationsDiffCommand');
        $command->setArguments([$provider]);
        $command->addTag('console.command');

        $container->setDefinition(static::DIFF_COMMAND_ID, $command);

        // Required if Symfony's compiler pass has already handled the "console.command" tags
        if ($container->hasParameter('console.command.ids')) {
            $ids = $container->getParameter('console.command.ids');
            $ids[] = static::DIFF_COMMAND_ID;

            $container->setParameter('console.command.ids', $ids);
        }
    }
}
