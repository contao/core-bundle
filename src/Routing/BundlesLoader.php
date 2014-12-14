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
use Symfony\Component\Finder\Finder;
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
     * The service IDs of all contao.route.loader tagged services.
     *
     * @var array
     */
    private $serviceIds = [];

    /**
     * Flag to remember if this loader is already loaded.
     *
     * @var bool
     */
    private $loaded = false;

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
     * Set the service IDs of all contao.route.loader tagged services.
     *
     * @param array $serviceIds The service IDs.
     *
     * @return static
     */
    public function setServiceIds(array $serviceIds)
    {
        $this->serviceIds = $serviceIds;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('You must not load the routing resource "contao_bundles" twice');
        }

        $collection = new RouteCollection();

        foreach ($this->serviceIds as $serviceId) {
            /** @var LoaderInterface $loader */
            $loader = $this->container->get($serviceId);
            $routes = $loader->load(null);

            $collection->addCollection($routes);
        }

        $this->loaded = true;

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'contao_bundles' === $type;
    }
}
