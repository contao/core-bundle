<?php

namespace Contao\CoreBundle\Routing;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
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
     * @var PageModel
     */
    private $pageAdapter;

    /**
     * @var Input
     */
    private $input;


    public function __construct(ContaoFrameworkInterface $framework, $input, string $urlSuffix, bool $prependLocale, bool $folderUrl, bool $useAutoItem)
    {
        $this->framework = $framework;

        $this->urlSuffix = $urlSuffix;
        $this->prependLocale = $prependLocale;
        $this->folderUrl = $folderUrl;
        $this->useAutoItem = $useAutoItem;

        $this->pageAdapter = $framework->getAdapter(PageModel::class);
        $this->input = $input;
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
        // TODO: Implement getRouteCollectionForRequest() method.
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
        // TODO: Implement getRouteByName() method.
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
        // TODO: Implement getRoutesByNames() method.
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

            // Return false if the URL suffix does not match (see #2864)
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


    private function isRootPage(string $pathInfo)
    {
        if ('' === $pathInfo) {
            return true;
        }

        if ($this->prependLocale && preg_match('@^([a-z]{2}(-[A-Z]{2})?)/$@', $pathInfo)) {
            return true;
        }

        return false;
    }
}
