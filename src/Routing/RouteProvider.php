<?php

namespace Contao\CoreBundle\Routing;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Input;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Connection
     */
    private $database;

    /**
     * @var string
     */
    private $urlSuffix;

    /**
     * @var bool
     */
    private $prependLocale;

    /**
     * @var bool
     */
    private $folderUrl;

    /**
     * @var bool
     */
    private $useAutoItem;

    /**
     * @var bool
     */
    private $redirectEmpty;

    /**
     * @var PageModel
     */
    private $pageAdapter;

    /**
     * @var Input
     */
    private $input;


    public function __construct(ContaoFrameworkInterface $framework, Connection $database, $input, string $urlSuffix, bool $prependLocale, bool $folderUrl, bool $useAutoItem, bool $doNotRedirectEmpty)
    {
        $this->framework = $framework;
        $this->database = $database;

        $this->urlSuffix = $urlSuffix;
        $this->prependLocale = $prependLocale;
        $this->folderUrl = $folderUrl;
        $this->useAutoItem = $useAutoItem;
        $this->redirectEmpty = !$doNotRedirectEmpty;

        $this->pageAdapter = $framework->getAdapter(PageModel::class);
        $this->input = $input;
        // TODO drop $input
    }

    /**
     * Finds routes that may potentially match the request.
     * This may return a mixed list of class instances, but all routes returned
     * must extend the core symfony route. The classes may also implement
     * RouteObjectInterface to link to a content document.
     * This method may not throw an exception based on implementation specific
     * restrictions on the url. That case is considered a not found - returning
     * an empty array. Exceptions are only used to abort the whole request in
     * case something is seriously broken, like the storage backend being down.
     * Note that implementations may not implement an optimal matching
     * algorithm, simply a reasonable first pass.  That allows for potentially
     * very large route sets to be filtered down to likely candidates, which
     * may then be filtered in memory more completely.
     *
     * @param Request $request A request against which to match
     *
     * @return RouteCollection with all Routes that could potentially match
     *                         $request. Empty collection if nothing can match
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $this->framework->initialize();
        $pathInfo = $request->getPathInfo();

        // The request string must not contain "auto_item" (see #4012)
        if (false !== strpos($pathInfo, '/auto_item/')) {
            return new RouteCollection();
        }

        if ('/' === $pathInfo
            || ($this->prependLocale && preg_match('@^/([a-z]{2}(-[A-Z]{2})?)/$@', $pathInfo))
        ) {
            $routes = [];

            $this->addRoutesForRootPages($this->findRootPages(), $routes);

            return $this->createCollectionForRoutes($routes);
        }

        $pathInfo = $this->removeSuffixAndLanguage($pathInfo);

        if (null === $pathInfo) {
            return new RouteCollection();
        }

        $routes = [];
        $candidates = $this->getAliasCandidates($pathInfo);
        $pages = $this->findPages($candidates);

        $this->addRoutesForPages($pages, $routes);

        return $this->createCollectionForRoutes($routes);
    }

    /**
     * Find the route using the provided route name.
     *
     * @param string $name The route name to fetch
     *
     * @return Route
     * @throws RouteNotFoundException If there is no route with that name in
     *                                this repository
     */
    public function getRouteByName($name)
    {
        $this->framework->initialize();

        $ids = $this->getPageIdsFromNames([$name]);

        if (empty($ids)) {
            throw new RouteNotFoundException('Route name does not match a page ID');
        }

        $routes = [];
        $page = $this->pageAdapter->findByPk($ids[0]);

        if (null === $page) {
            throw new RouteNotFoundException('Page ID '.$ids[0].' not found');
        }

        $this->addRoutesForPage($page, $routes);

        return $routes[$name];
    }

    /**
     * Find many routes by their names using the provided list of names.
     * Note that this method may not throw an exception if some of the routes
     * are not found or are not actually Route instances. It will just return the
     * list of those Route instances it found.
     * This method exists in order to allow performance optimizations. The
     * simple implementation could be to just repeatedly call
     * $this->getRouteByName() while catching and ignoring eventual exceptions.
     * If $names is null, this method SHOULD return a collection of all routes
     * known to this provider. If there are many routes to be expected, usage of
     * a lazy loading collection is recommended. A provider MAY only return a
     * subset of routes to e.g. support paging or other concepts, but be aware
     * that the DynamicRouter will only call this method once per
     * DynamicRouter::getRouteCollection() call.
     *
     * @param array|null $names The list of names to retrieve, In case of null,
     *                          the provider will determine what routes to return
     *
     * @return Route[] Iterable list with the keys being the names from the
     *                 $names array
     */
    public function getRoutesByNames($names)
    {
        $this->framework->initialize();

        if (null === $names) {
            $pages = $this->pageAdapter->findAll();
        } else {
            $ids = $this->getPageIdsFromNames($names);

            if (empty($ids)) {
                return [];
            }

            $pages = $this->pageAdapter->findBy('tl_page.id IN ('.implode(',', $ids).')', []);
        }

        $routes = [];

        $this->addRoutesForPages($pages, $routes);
        $this->sortRoutes($routes);

        return $routes;
    }

    /**
     * Split the current request into fragments, strip the URL suffix, recreate the $_GET array and return the page ID
     *
     * @return mixed
     */
    public function getPageIdFromUrl(Request $request)
    {
//        $strRequest = Environment::get('relativeRequest');
        $strRequest = substr($request->getPathInfo(), 1);
//        $strRequest = 0 === strncmp($strRequest, '/', 1) ? substr($strRequest, 1) : $strRequest;

        if ($strRequest == '') {
            return null;
        }

        // Get the request without the query string
        list($strRequest) = explode('?', $strRequest, 2);

        // URL decode here (see #6232)
        $strRequest = rawurldecode($strRequest);

        // The request string must not contain "auto_item" (see #4012)
        if (strpos($strRequest, '/auto_item/') !== false) {
            throw new \RuntimeException('The request string must not contain "auto_item"');
        }

        // Remove the URL suffix if not just a language root (e.g. en/) is requested
        if ($strRequest != '' && (!$this->prependLocale || !preg_match('@^[a-z]{2}(-[A-Z]{2})?/$@', $strRequest))) {
            $intSuffixLength = \strlen($this->urlSuffix);

            // Return if the URL suffix does not match (see #2864)
            if ($intSuffixLength > 0) {
                if (substr($strRequest, -$intSuffixLength) != $this->urlSuffix) {
                    throw new \RuntimeException('The URL suffix does not match');
                }

                $strRequest = substr($strRequest, 0, -$intSuffixLength);
            }
        }

        // Extract the language
        if ($this->prependLocale) {
            $arrMatches = array();

            // Use the matches instead of substr() (thanks to Mario Müller)
            if (preg_match('@^([a-z]{2}(-[A-Z]{2})?)/(.*)$@', $strRequest, $arrMatches)) {
                $this->input->setGet('language', $arrMatches[1]);

                // Trigger the root page if only the language was given
                if ($arrMatches[3] == '') {
                    return null;
                }

                $strRequest = $arrMatches[3];
            } else {
                throw new \RuntimeException('Language not provided');
            }
        }

        $arrFragments = null;

        // Use folder-style URLs
        if ($this->folderUrl && strpos($strRequest, '/') !== false) {
            $strAlias = $strRequest;
            $arrOptions = array($strAlias);

            // Compile all possible aliases by applying dirname() to the request (e.g. news/archive/item, news/archive, news)
            while ($strAlias != '/' && strpos($strAlias, '/') !== false) {
                $strAlias = \dirname($strAlias);
                $arrOptions[] = $strAlias;
            }

            // Check if there are pages with a matching alias
            $objPages = $this->pageAdapter->findByAliases($arrOptions);

            if ($objPages !== null) {
                $arrPages = array();

                // Order by domain and language
                while ($objPages->next()) {
                    /** @var PageModel $objModel */
                    $objModel = $objPages->current();
                    $objPage  = $objModel->loadDetails();

                    $domain = $objPage->domain ?: '*';
                    $arrPages[$domain][$objPage->rootLanguage][] = $objPage;

                    // Also store the fallback language
                    if ($objPage->rootIsFallback) {
                        $arrPages[$domain]['*'][] = $objPage;
                    }
                }

//                $strHost = \Environment::get('host');
                $strHost = $request->getHost();

                // Look for a root page whose domain name matches the host name
                if (isset($arrPages[$strHost])) {
                    $arrLangs = $arrPages[$strHost];
                } else {
                    $arrLangs = $arrPages['*'] ?: array(); // empty domain
                }

                $arrAliases = array();

                if (!$this->prependLocale) {
                    // Use the first result (see #4872)
                    $arrAliases = current($arrLangs);
                } elseif (($lang = $this->input->get('language')) && isset($arrLangs[$lang])) {
                    // Try to find a page matching the language parameter
                    $arrAliases = $arrLangs[$lang];
                }

                // Return if there are no matches
                if (empty($arrAliases)) {
                    throw new \RuntimeException('No matches for folder URL');
                }

                $objPage = $arrAliases[0];

                if ($strRequest == $objPage->alias) {
                    // The request consists of the alias only
                    $arrFragments = array($strRequest);
                } else {
                    // Remove the alias from the request string, explode it and then re-insert the alias at the beginning
                    $arrFragments = explode('/', substr($strRequest, \strlen($objPage->alias) + 1));
                    array_unshift($arrFragments, $objPage->alias);
                }
            }
        }

        // If folderUrl is deactivated or did not find a matching page
        if ($arrFragments === null) {
            if ($strRequest == '/') {
                throw new \RuntimeException('Did not find a matching page');
            } else {
                $arrFragments = explode('/', $strRequest);
            }
        }

        // Add the second fragment as auto_item if the number of fragments is even
        if ($this->useAutoItem && \count($arrFragments) % 2 == 0) {
            array_insert($arrFragments, 1, array('auto_item'));
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && \is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback)
            {
                $arrFragments = System::importStatic($callback[0])->{$callback[1]}($arrFragments);
            }
        }

        // Return if the alias is empty (see #4702 and #4972)
        if ($arrFragments[0] == '' && \count($arrFragments) > 1) {
            throw new \RuntimeException('The alias is empty');
        }

        // Add the fragments to the $_GET array
        for ($i=1, $c=\count($arrFragments); $i<$c; $i+=2) {
            // Skip key value pairs if the key is empty (see #4702)
            if ($arrFragments[$i] == '') {
                continue;
            }

            // Return false if there is a duplicate parameter (duplicate content) (see #4277)
            if (isset($_GET[$arrFragments[$i]])) {
                throw new \RuntimeException('Duplicate parameter in query');
            }

            // Return false if the request contains an auto_item keyword (duplicate content) (see #4012)
            if ($this->useAutoItem && \in_array($arrFragments[$i], $GLOBALS['TL_AUTO_ITEM'])) {
                throw new \RuntimeException('Request contains an auto_item keyword');
            }

            $this->input->setGet(urldecode($arrFragments[$i]), urldecode($arrFragments[$i+1]), true);
        }

        return $arrFragments[0] ?: null;
    }

    private function removeSuffixAndLanguage(string $pathInfo)
    {
        $suffixLength = \strlen($this->urlSuffix);

        if ($suffixLength !== 0) {
            if (substr($pathInfo, -$suffixLength) !== $this->urlSuffix) {
                return null;
            }

            $pathInfo = substr($pathInfo, 0, -$suffixLength);
        }

        if (0 === strncmp($pathInfo, '/', 1)) {
            $pathInfo = substr($pathInfo, 1);
        }

        if ($this->prependLocale) {
            $matches = array();

            if (preg_match('@^([a-z]{2}(-[A-Z]{2})?)/(.+)$@', $pathInfo, $matches)) {
                $pathInfo = $matches[3];
            } else {
                return null;
            }
        }

        return $pathInfo;
    }

    /**
     * Compile all possible aliases by applying dirname() to the request (e.g. news/archive/item, news/archive, news).
     *
     * @param string $pathInfo
     *
     * @return array
     */
    private function getAliasCandidates(string $pathInfo)
    {
        $pos = strpos($pathInfo, '/');

        if (false === $pos) {
            return [$pathInfo];
        }

        if (!$this->folderUrl) {
            return [substr($pathInfo, 0, $pos)];
        }

        $candidates = [$pathInfo];

        while ('/' !== $pathInfo && false !== strpos($pathInfo, '/')) {
            $pathInfo = \dirname($pathInfo);
            $candidates[] = $pathInfo;
        }

        return $candidates;
    }

    private function addRoutesForPages($pages, array &$routes): void
    {
        if (null === $pages) {
            return;
        }

        /** @var PageModel $page */
        foreach ($pages as $page) {
            $this->addRoutesForPage($page, $routes);
        }
    }

    private function addRoutesForRootPages(array $pages, array &$routes): void
    {
        if (null === $pages) {
            return;
        }

        /** @var PageModel $page */
        foreach ($pages as $page) {
            $this->addRoutesForRootPage($page, $routes);
        }
    }

    private function createCollectionForRoutes(array $routes): RouteCollection
    {
        $this->sortRoutes($routes);

        $collection = new RouteCollection();

        foreach ($routes as $name => $route) {
            $collection->add($name, $route);
        }

        return $collection;
    }

    private function addRoutesForPage(PageModel $page, array &$routes)
    {
        $page->loadDetails();

        $defaults = $this->getRouteDefaults($page);
        $defaults['parameters'] = '';
        $requirements = ['parameters' => '.*'];

        $path = sprintf('/%s{parameters}%s', $page->alias ?: $page->id, $this->urlSuffix);

        if ($this->prependLocale) {
            $path = '/{_locale}'.$path;
//            $requirements['_locale'] = '[a-z]{2}(\-[A-Z]{2})?';
            $requirements['_locale'] = $page->rootLanguage;
        }

        $routes['tl_page.'.$page->id] = new Route(
            $path,
            $defaults,
            $requirements,
            ['utf8' => true],
            $page->domain ?: null,
            $page->rootUseSSL ? 'https' : null // TODO should we match SSL only if enabled in root?
        );

        $this->addRoutesForRootPage($page, $routes);
    }

    private function addRoutesForRootPage(PageModel $page, array &$routes)
    {
        if ('root' !== $page->type && 'index' !== $page->alias) {
            return;
        }

        $page->loadDetails();

        if (!$this->prependLocale && 'index' !== $page->alias && !$page->rootIsFallback) {
            return;
        }

        $path = '/';
        $requirements = [];
        $defaults = $this->getRouteDefaults($page);

        if ($this->prependLocale) {
            $path = '/{_locale}'.$path;
            $requirements['_locale'] = $page->rootLanguage;
        }

        $routes['tl_page.'.$page->id.'.root'] = new Route(
            $path,
            $defaults,
            $requirements,
            [],
            $page->domain ?: null,
            $page->rootUseSSL ? 'https' : null // TODO should we match SSL only if enabled in root?
        );

        if ($this->prependLocale && $page->rootIsFallback) {
            if ($this->redirectEmpty) {
                $defaults['_controller'] = 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction';
                $defaults['path'] = '/'.$page->language.'/';
                $defaults['permanent'] = true;
            }

            $routes['tl_page.'.$page->id.'.fallback'] = new Route(
                '/',
                $defaults,
                [],
                [],
                $page->domain ?: null,
                $page->rootUseSSL ? 'https' : null // TODO should we match SSL only if enabled in root?
            );
        }
    }

    private function getRouteDefaults(PageModel $page): array
    {
        return [
            '_token_check' => true,
            '_controller' => 'Contao\FrontendIndex::renderPage', //'Contao\CoreBundle\Controller\FrontendController::indexAction',
            '_scope' => ContaoCoreBundle::SCOPE_FRONTEND,
            '_locale' => $page->rootLanguage,
            'pageModel' => $page,
        ];
    }

    private function getPageIdsFromNames(array $names)
    {
        $ids = [];

        foreach ($names as $name) {
            if (0 !== strncmp($name, 'tl_page.', 8)) {
                continue;
            }

            list(, $id) = explode('.', $name);

            $ids[] = $id;
        }

        return array_unique($ids);
    }

    /**
     * Sorts routes so that the FinalMatcher will correctly resolve them.
     * 1. The ones with hostname should come first so the empty ones are only taken if no hostname matches
     * 2. Root pages come last so non-root page with index alias (= identical path) matches first
     * 3. Pages with longer alias (folder page) must come first to match if applicable
     *
     * @param array $routes
     */
    private function sortRoutes(array &$routes)
    {
        uasort($routes, function (Route $a, Route $b) {
            if ('' !== $a->getHost() && '' === $b->getHost()) {
                return -1;
            }

            if ('' === $a->getHost() && '' !== $b->getHost()) {
                return 1;
            }

            $pageA = $a->getDefault('pageModel');
            $pageB = $b->getDefault('pageModel');

            if (!$pageA instanceof PageModel || !$pageB instanceof PageModel) {
                return 0;
            }

            if ('root' !== $pageA->type && 'root' === $pageB->type) {
                return -1;
            }

            if ('root' === $pageA->type && 'root' !== $pageB->type) {
                return 1;
            }

            return strnatcasecmp($pageB->alias, $pageA->alias);
        });
    }

    private function findPages(array $candidates)
    {
        $ids = [];
        $aliases = [];

        foreach ($candidates as $candidate) {
            if (is_numeric($candidate)) {
                $ids[] = (int) $candidate;
            } else {
                $aliases[] = $this->database->quote($candidate);
            }
        }

        $table = $this->pageAdapter->getTable();
        $conditions = [];

        if (!empty($ids)) {
            $conditions[] = $table.'.id IN ('.implode(',', $ids).')';
        }

        if (!empty($aliases)) {
            $conditions[] = $table.'.alias IN ('.implode(',', $aliases).')';
        }

        $pages = $this->pageAdapter->findBy([implode(' OR ', $conditions)], []);

        if ($pages instanceof Collection) {
            return $pages->getModels();
        }

        return [];
    }

    private function findRootPages(): array
    {
        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getRootPageFromUrl']) && \is_array($GLOBALS['TL_HOOKS']['getRootPageFromUrl'])) {
            /** @var System $systemAdapter */
            $systemAdapter = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_HOOKS']['getRootPageFromUrl'] as $callback) {
                $page = $systemAdapter->importStatic($callback[0])->{$callback[1]}();

                /** @var PageModel $page */
                if ($page instanceof PageModel) {
                    return [$page];
                }
            }
        }

        // Include pages with alias "index" or "/" (see #8498, #8560 and #1210)
        $pages = $this->pageAdapter->findBy(["tl_page.type='root' OR tl_page.alias='index' OR tl_page.alias='/'"], []);

        if ($pages instanceof Collection) {
            return $pages->getModels();
        }

        return [];
    }
}
