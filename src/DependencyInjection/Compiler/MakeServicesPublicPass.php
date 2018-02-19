<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Makes services public that we need to retrieve directly.
 */
class MakeServicesPublicPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $services = [
            'assets.packages',
            'database_connection',
            'doctrine.dbal.default_connection',
            'fragment.handler',
            'lexik_maintenance.driver.factory',
            'monolog.logger.contao',
            'security.firewall.map',
            'security.logout_url_generator',
            'swiftmailer.mailer'
        ];

        foreach ($services as $service) {
            // Uses findDefinition instead of hasDefinition/getDefinition
            // as hasDefinition does not check aliased services
            try {
                $definition = $container->findDefinition($service);
                $definition->setPublic(true);
            } catch (ServiceNotFoundException $exception) {
                continue;
            }
        }
    }
}
