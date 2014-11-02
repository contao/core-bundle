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

use Contao\Config;
use Contao\CoreBundle\Event\CoreBundleEvents;
use Contao\CoreBundle\Event\CreatePageRouteEvent;
use Contao\PageModel;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamically adds routes
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class PageRouteProvider implements RouteProviderInterface
{
    /**
     * The contao configuration.
     *
     * @var Config
     */
    private $config;

    /**
     * The candidates strategy.
     *
     * @var CandidatesInterface
     */
    private $candidatesStrategy;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        Config $config,
        CandidatesInterface $candidatesStrategy,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->config             = $config;
        $this->candidatesStrategy = $candidatesStrategy;
        $this->eventDispatcher    = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = new RouteCollection();

        $candidates = $this->candidatesStrategy->getCandidates($request);
        if (empty($candidates)) {
            return $collection;
        }
        $routes = $this->findByStaticPrefix($candidates);
        /** @var $route SymfonyRoute */
        foreach ($routes as $name => $route) {
            $collection->add($name, $route);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name)
    {
        if (!preg_match('~^contao_page_(\d+)$~', $name, $matches)) {
            throw new RouteNotFoundException(sprintf('Route "%s" is not handled by this route provider', $name));
        }

        $route = $this->findByPageId($matches[1]);
        if (!$route) {
            throw new RouteNotFoundException("No route found for name '$name'");
        }

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names = null)
    {

        if (null === $names) {
            return $this->findByStaticPrefix(array());
        }

        $routes = array();
        foreach ($names as $name) {
            try {
                $routes[] = $this->getRouteByName($name);
            } catch (RouteNotFoundException $e) {
                // not found
            }
        }

        return $routes;
    }

    /**
     * Find one route by page ID.
     *
     * @param int $id The page ID.
     *
     * @return SymfonyRoute|null The route or null if no route was found.
     */
    private function findByPageId($id)
    {
        $page = PageModel::findByPk($id);

        if (!$page) {
            return null;
        }

        $routes = [];
        $this->createRoutesFromPage($page, $routes);

        return array_shift($routes);
    }

    /**
     * Find multiple page routes by static aliases.
     *
     * @param array $aliases The aliases.
     *
     * @return array A set of routes.
     */
    private function findByStaticPrefix(array $aliases)
    {
        if (empty($aliases)) {
            $pages = PageModel::findAll(['order' => 'type="root", alias DESC']);
        } else {
            $where      = array_fill(0, count($aliases), 'alias=?');
            $parameters = array_map(
                function ($candidate) {
                    return (string) substr($candidate, 1);
                },
                $aliases
            );

            if ($this->requireRootPageRoutes($aliases)) {
                $where[]      = 'type=?';
                $parameters[] = 'root';
            }

            $where = implode(' OR ', $where);
            $where = [$where];
            $pages = PageModel::findBy($where, $parameters, ['order' => 'type="root", alias DESC']);
        }

        $routes = [];

        if ($pages) {
            foreach ($pages as $page) {
                /** @var PageModel $page */
                $page->loadDetails();
                $page->preventSaving();

                $this->createRoutesFromPage($page, $routes);
            }
        }

        return $routes;
    }

    /**
     * Create routes for the given $page and add them to $routes.
     *
     * @param PageModel $page   The page model.
     * @param array     $routes The routes collection array.
     */
    private function createRoutesFromPage(PageModel $page, array &$routes)
    {
        $event = new CreatePageRouteEvent($page);
        $this->eventDispatcher->dispatch(CoreBundleEvents::CREATE_PAGE_ROUTES, $event);

        foreach ($event->getRoutes() as $name => $route) {
            $routes[$name] = $route;
        }
    }

    /**
     * Determine if the root page routes are required to be generated.
     *
     * The root page routes are required if $candidates contain the page route, e.g. "/" or "/en" or "/en/".
     *
     * @param array $candidates The candidates to generate routes for.
     *
     * @return bool
     */
    private function requireRootPageRoutes(array $candidates)
    {
        if (in_array('/', $candidates)) {
            return true;
        }

        if ($this->config->get('addLanguageToUrl')) {
            foreach ($candidates as $candidate) {
                if (preg_match('~^/\w\w(-\w\w)?/?$~', $candidate)) {
                    return true;
                }
            }
        }

        return false;
    }
}
