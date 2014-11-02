<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Event;

use Contao\PageModel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;

class CreatePageRouteEvent extends Event
{
    /**
     * The page model.
     *
     * @var PageModel
     */
    private $page;

    /**
     * The page routes.
     *
     * @var array|Route[]
     */
    private $routes = [];

    public function __construct(PageModel $page)
    {
        $this->page = $page;
    }

    /**
     * Return the page model.
     *
     * @return PageModel The page model.
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Return the page routes.
     *
     * @return array|Route[] The page routes.
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Determine if a route for the name exist.
     *
     * @param string $name The route name.
     *
     * @return bool
     *
     * @throws \InvalidArgumentException If the route name is invalid.
     */
    public function hasRoute($name)
    {
        if (is_numeric($name)) {
            throw new \InvalidArgumentException('Route name cannot be numeric');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Route name cannot be empty');
        }

        return isset($this->routes[$name]);
    }

    /**
     * Determine if a route for the name exist.
     *
     * @param string $name The route name.
     *
     * @return Route
     *
     * @throws \InvalidArgumentException If the route name is invalid.
     * @throws \InvalidArgumentException If the route does not exists.
     */
    public function getRoute($name)
    {
        if (is_numeric($name)) {
            throw new \InvalidArgumentException('Route name cannot be numeric');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Route name cannot be empty');
        }
        if (!isset($this->routes[$name])) {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist', $name));
        }

        return $this->routes[$name];
    }

    /**
     * Add a page route.
     *
     * @param string $name  The route name.
     * @param Route  $route The page route.
     *
     * @return static
     *
     * @throws \InvalidArgumentException If the route name is invalid.
     * @throws \InvalidArgumentException If the route already exists.
     */
    public function addRoute($name, Route $route)
    {
        if (is_numeric($name)) {
            throw new \InvalidArgumentException('Route name cannot be numeric');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Route name cannot be empty');
        }
        if (isset($this->routes[$name])) {
            throw new \InvalidArgumentException(sprintf('Route "%s" already exists', $name));
        }

        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * Replace a page route.
     *
     * @param string $name  The route name.
     * @param Route  $route The page route.
     *
     * @return static
     *
     * @throws \InvalidArgumentException If the route name is invalid.
     * @throws \InvalidArgumentException If the route does not exists.
     */
    public function replaceRoute($name, Route $route)
    {
        if (is_numeric($name)) {
            throw new \InvalidArgumentException('Route name cannot be numeric');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Route name cannot be empty');
        }
        if (!isset($this->routes[$name])) {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist', $name));
        }

        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * Set the page routes.
     *
     * @param array|Route[] $routes The page routes.
     *
     * @return static
     */
    public function setRoutes(array $routes)
    {
        $this->routes = [];
        $this->addRoutes($routes);
        return $this;
    }

    /**
     * Add page routes.
     *
     * @param array|Route[] $routes The page routes.
     *
     * @return static
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }
        return $this;
    }
}
