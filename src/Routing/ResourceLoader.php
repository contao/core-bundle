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

use Symfony\Component\Config\Loader\Loader as BaseLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add routes from a resource.
 *
 * @author Tristan Lins <https://github.com/tristan.lins>
 */
class ResourceLoader extends BaseLoader
{
    /**
     * The routing loader service, that can handle different resource formats.
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * The resource to load.
     *
     * @var string
     */
    private $resource;

    /**
     * Type key to respond to.
     *
     * @var string
     */
    private $type;

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
     * @param string             $resource  The resource to load.
     * @param string             $type      Type key to respond to.
     */
    public function __construct(LoaderInterface $loader, $resource, $type = null)
    {
        $this->loader   = $loader;
        $this->resource = $resource;
        $this->type     = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException(
                sprintf(
                    'You must not load the routing resource "%s" twice',
                    $this->resource
                )
            );
        }

        $routes = $this->loader->load($this->resource);

        $this->loaded = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $this->type && $this->type === $type;
    }
}
