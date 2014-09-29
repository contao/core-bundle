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

namespace Contao\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds the Contao configuration
 *
 * @author Leo Feyer <https://contao.org>
 */
class AddContaoConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Add the database settings
        $container->setParameter('database_driver', 'pdo_mysql');
        $container->setParameter('database_host', $GLOBALS['TL_CONFIG']['dbHost']);
        $container->setParameter('database_port', $GLOBALS['TL_CONFIG']['dbPort']);
        $container->setParameter('database_name', $GLOBALS['TL_CONFIG']['dbDatabase']);
        $container->setParameter('database_user', $GLOBALS['TL_CONFIG']['dbUser']);
        $container->setParameter('database_password', $GLOBALS['TL_CONFIG']['dbPass']);

        // Add the mailer settings
        if (!$GLOBALS['TL_CONFIG']['useSMTP']) {
            $container->setParameter('mailer_transport', 'mail');
        } else {
            $container->setParameter('mailer_transport', 'smtp');
            $container->setParameter('mailer_host', $GLOBALS['TL_CONFIG']['smtpHost']);
            $container->setParameter('mailer_user', $GLOBALS['TL_CONFIG']['smtpUser']);
            $container->setParameter('mailer_password', $GLOBALS['TL_CONFIG']['smtpPass']);
        }

        // Use the encryption key as kernel secret
        $container->setParameter('kernel.secret', $GLOBALS['TL_CONFIG']['encryptionKey']);

        // Set the default charset
        $container->setParameter('kernel.charset', $GLOBALS['TL_CONFIG']['characterSet']);
    }
}
