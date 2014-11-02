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
use Contao\CoreBundle\Event\CoreModuleEvents;
use Contao\CoreBundle\Event\CreatePageRouteEvent;
use Contao\PageModel;
use Symfony\Cmf\Bundle\RoutingBundle\Model\RedirectRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route as RouteModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Create page routes for standard page types.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class CreatePageRoutesSubscriber implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [CoreModuleEvents::CREATE_PAGE_ROUTES => 'createPageRoutes'];
    }

    /**
     * Create page routes depending on the page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    public function createPageRoutes(CreatePageRouteEvent $event)
    {
        switch ($event->getPage()->type) {
            case 'regular':
                $this->createRegularPageRoute($event);
                break;

            case 'forward':
                $this->createForwardPageRoute($event);
                break;

            case 'redirect':
                $this->createRedirectPageRoute($event);
                break;

            case 'error_403':
                $this->createError403PageRoute($event);
                break;

            case 'error_404':
                $this->createError404PageRoute($event);
                break;

            case 'root':
                $this->createRootPageRoute($event);
                break;

            default:
                if (!$event->getRoutes()) {
                    // Fallback for custom types
                    $this->createCustomPageRoute($event);
                }
        }
    }

    /**
     * Create route for regular page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createRegularPageRoute(CreatePageRouteEvent $event)
    {
        // TODO handle alias "index" ???

        $page    = $event->getPage();
        $pattern = '/' . $page->alias . '{_auto_item}{_path_parameters}';

        $name  = 'contao_page_' . $page->id;
        $route = $this->createRouteModel($page, $pattern);
        $event->addRoute($name, $route);
    }

    /**
     * Create route for forward page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createForwardPageRoute(CreatePageRouteEvent $event)
    {
        $page    = $event->getPage();
        $pattern = '/' . $page->alias . '{_auto_item}{_path_parameters}';

        if ($page->jumpTo) {
            $targetPage = PageModel::findByPk($page->jumpTo);
        } else {
            $targetPage = PageModel::findFirstPublishedByPid($page->id);
        }

        if (!$targetPage) {
            // TODO redirect to 404?
            throw new \RuntimeException(sprintf('Forward page ID %d has no usable target', $page->id));
        }

        // TODO: is this pattern necessary?
        $targetPattern = '/' . $targetPage->alias . '{_auto_item}{_path_parameters}';
        $targetRoute   = $this->createRouteModel($targetPage, $targetPattern);

        $name  = 'contao_page_' . $page->id;
        $route = $this->$this->createInternalRedirectRoute(
            $page,
            $pattern,
            'contao_page_' . $page->id,
            $targetRoute
        );
        $event->addRoute($name, $route);
    }

    /**
     * Create route for redirect page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createRedirectPageRoute(CreatePageRouteEvent $event)
    {
        $page      = $event->getPage();
        $pattern   = '/' . $page->alias . '{_auto_item}{_path_parameters}';
        $targetUri = $page->url;

        $name  = 'contao_page_' . $page->id;
        $route = $this->createExternalRedirectRoute($page, $pattern, $targetUri);
        $event->addRoute($name, $route);
    }

    /**
     * Create route for error_403 page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createError403PageRoute(CreatePageRouteEvent $event)
    {
        $this->createRegularPageRoute($event);
    }

    /**
     * Create route for error_404 page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createError404PageRoute(CreatePageRouteEvent $event)
    {
        $this->createRegularPageRoute($event);
    }

    /**
     * Create route for root page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createRootPageRoute(CreatePageRouteEvent $event)
    {
        $page    = $event->getPage();
        $pattern = '/';

        $homePage = PageModel::findFirstPublishedByPid($page->id);

        if (!$homePage) {
            // TODO redirect to 404???
            return;
        }

        /** @var PageModel $homePage */
        $homePage->loadDetails();
        $homePage->preventSaving();

        if ('index' !== $homePage->alias) {
            // redirect to home page
            $route = $this->createInternalRedirectRoute($page, $pattern, 'contao_page_' . $homePage->id);
            $route->setOption('add_format_pattern', false);
        } else {
            // internal bypass
            $route = $this->createRouteModel($homePage, $pattern);
            $route->setOption('add_format_pattern', false);
        }

        $name = 'contao_page_' . $page->id;
        $event->addRoute($name, $route);

        if ($this->hasLocalePattern()) {
            // add redirect when requested without trailing slash
            $redirectRoute = $this->createInternalRedirectRoute($page, '', $name, $route);
            $redirectName  = $name . '_no_trailing_slash';
            $event->addRoute($redirectName, $redirectRoute);

            // add redirect when requested without locale pattern
            $redirectRoute = $this->createInternalRedirectRoute($page, '/', $name, $route);
            $redirectRoute->setOption('add_locale_pattern', false);
            $redirectName = $name . '_no_locale';
            $event->addRoute($redirectName, $redirectRoute);
        }
    }

    /**
     * Create route for custom page type.
     *
     * @param CreatePageRouteEvent $event The event object.
     */
    private function createCustomPageRoute(CreatePageRouteEvent $event)
    {
        $page    = $event->getPage();
        $pattern = '/' . $page->alias . '{_auto_item}{_path_parameters}';

        $name  = 'contao_page_' . $page->id;
        $route = $this->createRouteModel($page, $pattern);
        $route->setDefaults(['_controller' => 'contao.controllers.frontend:customPageAction'] + $route->getDefaults());
        $event->addRoute($name, $route);
    }

    /**
     * Create a page route.
     *
     * @param PageModel $page    The page model.
     * @param string    $pattern The url pattern.
     *
     * @return RouteModel The route for the given page.
     */
    private function createRouteModel(PageModel $page, $pattern)
    {
        $defaults     = $this->getDefaults($page);
        $requirements = $this->getRequirements();

        $route = new RouteModel();
        $route->setOption('add_format_pattern', $this->hasFormatPattern());
        $route->setOption('add_locale_pattern', $this->hasLocalePattern());
        $route->setDefaults(['type' => $page->type] + $defaults);
        $route->setRequirements($requirements);
        $route->setVariablePattern($pattern);
        $route->setHost($page->domain);
        $route->setContent($page);

        return $route;
    }

    /**
     * Create a redirect route for the given page, that redirects to another page.
     *
     * @param PageModel    $page            The page model.
     * @param string       $pattern         The url pattern.
     * @param string       $targetRouteName The target route name.
     * @param SymfonyRoute $targetRoute     The target route.
     *
     * @return RedirectRoute The redirect route for the given page.
     */
    private function createInternalRedirectRoute(
        PageModel $page,
        $pattern,
        $targetRouteName,
        SymfonyRoute $targetRoute = null
    ) {
        $defaults     = $this->getDefaults($page);
        $requirements = $this->getRequirements();

        $route = new RedirectRoute();
        $route->setOption('add_locale_pattern', $this->hasLocalePattern());
        $route->setDefaults($defaults);
        $route->setRequirements($requirements);
        $route->setVariablePattern($pattern);
        $route->setHost($page->domain);
        $route->setRouteName($targetRouteName);

        if ($targetRoute) {
            $route->setRouteTarget($targetRoute);
        }

        return $route;
    }

    /**
     * Create a redirect route for the given page, that redirects to another page.
     *
     * @param PageModel $page      The page model.
     * @param string    $pattern   The url pattern.
     * @param string    $targetUri The target URI.
     *
     * @return RedirectRoute The redirect route for the given page.
     */
    private function createExternalRedirectRoute(PageModel $page, $pattern, $targetUri)
    {
        $defaults     = $this->getDefaults($page);
        $requirements = $this->getRequirements();

        $route = new RedirectRoute();
        $route->setOption('add_locale_pattern', $this->hasLocalePattern());
        $route->setDefaults($defaults);
        $route->setRequirements($requirements);
        $route->setVariablePattern($pattern);
        $route->setHost($page->domain);
        $route->setUri($targetUri);

        return $route;
    }

    /**
     * Create the route defaults for the given page.
     *
     * @param PageModel $page The page model.
     *
     * @return array
     */
    private function getDefaults(PageModel $page)
    {
        $defaults = [
            'type'             => $page->type,
            '_auto_item'       => '',
            '_path_parameters' => '',
            '_tl_script'       => 'index.php' // FIXME: legacy support
        ];

        $format = $this->getFormatPattern();
        if ($format) {
            $defaults['_format'] = $format;
        }

        return $defaults;
    }

    /**
     * Create the route requirements.
     *
     * @return array
     */
    private function getRequirements()
    {
        $requirements = [
            '_auto_item'       => '(/[^/]+)?',
            '_path_parameters' => '(/[^/]+/[^/]+?)*',
        ];

        $format = $this->getFormatPattern();
        if ($format) {
            $requirements['_format'] = $format;
        }

        if ($this->hasLocalePattern()) {
            $requirements['_locale'] = '[a-z]{2}(\-[A-Z]{2})?';
        }

        return $requirements;
    }

    /**
     * Return the {_format} pattern.
     *
     * @return string
     */
    private function getFormatPattern()
    {
        return substr($this->config->get('urlSuffix'), 1);
    }

    /**
     * Determine if the {_format} pattern is required.
     *
     * @return bool
     */
    private function hasFormatPattern()
    {
        return (bool) $this->getFormatPattern();
    }

    /**
     * Determine if the {_locale} pattern is required.
     *
     * @return bool
     */
    private function hasLocalePattern()
    {
        return (bool) $this->config->get('addLanguageToUrl');
    }

}
