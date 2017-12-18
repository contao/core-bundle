<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection;

use Contao\CoreBundle\Picker\PickerProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class ContaoCoreExtension extends ConfigurableExtension
{
    /**
     * @var array
     */
    private static $files = [
        'commands.yml',
        'listener.yml',
        'services.yml',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'contao';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        // Add the resource to the container
        parent::getConfiguration($config, $container);

        return new Configuration(
            $container->getParameter('kernel.debug'),
            $container->getParameter('kernel.project_dir'),
            $container->getParameter('kernel.root_dir'),
            $container->getParameter('kernel.default_locale')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        foreach (self::$files as $file) {
            $loader->load($file);
        }

        $container->setParameter('contao.web_dir', $mergedConfig['web_dir']);
        $container->setParameter('contao.prepend_locale', $mergedConfig['prepend_locale']);
        $container->setParameter('contao.encryption_key', $mergedConfig['encryption_key']);
        $container->setParameter('contao.url_suffix', $mergedConfig['url_suffix']);
        $container->setParameter('contao.upload_path', $mergedConfig['upload_path']);
        $container->setParameter('contao.csrf_token_name', $mergedConfig['csrf_token_name']);
        $container->setParameter('contao.pretty_error_screens', $mergedConfig['pretty_error_screens']);
        $container->setParameter('contao.error_level', $mergedConfig['error_level']);
        $container->setParameter('contao.locales', $mergedConfig['locales']);
        $container->setParameter('contao.image.bypass_cache', $mergedConfig['image']['bypass_cache']);
        $container->setParameter('contao.image.target_dir', $mergedConfig['image']['target_dir']);
        $container->setParameter('contao.image.valid_extensions', $mergedConfig['image']['valid_extensions']);
        $container->setParameter('contao.image.imagine_options', $mergedConfig['image']['imagine_options']);

        if (isset($mergedConfig['localconfig'])) {
            $container->setParameter('contao.localconfig', $mergedConfig['localconfig']);
        }

        $this->overwriteImageTargetDir($mergedConfig, $container);

        $container
            ->registerForAutoconfiguration(PickerProviderInterface::class)
            ->addTag('contao.picker_provider')
        ;
    }

    /**
     * Reads the old contao.image.target_path parameter.
     *
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     */
    private function overwriteImageTargetDir(array $mergedConfig, ContainerBuilder $container): void
    {
        if (!isset($mergedConfig['image']['target_path'])) {
            return;
        }

        $container->setParameter(
            'contao.image.target_dir',
            $container->getParameter('kernel.project_dir').'/'.$mergedConfig['image']['target_path']
        );

        @trigger_error('Using the contao.image.target_path parameter has been deprecated and will no longer work in Contao 5. Use the contao.image.target_dir parameter instead.', E_USER_DEPRECATED);
    }
}
