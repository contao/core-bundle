<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Routing;

use Contao\CoreBundle\HttpKernel\ContaoKernelInterface;
use Symfony\Component\Config\Loader\Loader as BaseLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouteCollection;

/**
 * Add routes from bundles.
 *
 * @author Tristan Lins <https://github.com/tristan.lins>
 */
class BundlesLoader extends BaseLoader
{
    /**
     * The service container.
     *
     * Passing the service container is necessary to solve circular reference between the routing.loader service
     * and this loader.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Create new bundles loader.
     *
     * @param ContainerInterface $container The service container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        /** @var ContaoKernelInterface $kernel */
        $kernel = $this->container->get('kernel');

        /** @var LoaderInterface $loader */
        $loader = $this->container->get('routing.loader');

        $bundles    = $kernel->getContaoBundles();
        $filesystem = new Filesystem();
        $collection = new RouteCollection();

        foreach ($bundles as $bundle) {
            foreach (['yml', 'yml', 'php'] as $extension) {
                // TODO: Not sure if the contao resource path is a good idea here
                $path = $bundle->getContaoResourcesPath() . '/config/routing.' . $extension;
                if ($filesystem->exists($path)) {
                    $routes = $loader->load($path);

                    foreach ($routes as $name => $route) {
                        $collection->add($name, $route);
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'bundles';
    }
}
