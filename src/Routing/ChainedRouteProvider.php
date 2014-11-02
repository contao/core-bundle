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

use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

/**
 * Load routes through multiple providers.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class ChainedRouteProvider implements RouteProviderInterface
{
    /**
     * The route providers in this chain.
     *
     * @var RouteProviderInterface[]
     */
    private $providers = [];

    /**
     * Add a provider to this chain.
     *
     * @param RouteProviderInterface $provider The route provider.
     */
    public function addProvider(RouteProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Get all route providers from this chain.
     *
     * @return RouteProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = new RouteCollection();

        foreach ($this->providers as $provider) {
            $routes = $provider->getRouteCollectionForRequest($request);
            $collection->addCollection($routes);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name)
    {
        $collection = new RouteCollection();

        foreach ($this->providers as $provider) {
            $routes = $provider->getRouteByName($name);
            $collection->addCollection($routes);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names = null)
    {
        $collection = new RouteCollection();

        foreach ($this->providers as $provider) {
            $routes = $provider->getRoutesByNames($names);
            $collection->addCollection($routes);
        }

        return $collection;
    }
}
