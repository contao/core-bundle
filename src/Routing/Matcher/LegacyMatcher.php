<?php

namespace Contao\CoreBundle\Routing\Matcher;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class LegacyMatcher implements RequestMatcherInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    /**
     * @var System
     */
    private $systemAdapter;

    /**
     * @var Input
     */
    private $inputAdapter;

    /**
     * @var Config
     */
    private $configAdapter;

    public function __construct(ContaoFrameworkInterface $framework, RequestMatcherInterface $requestMatcher)
    {
        $this->framework = $framework;
        $this->requestMatcher = $requestMatcher;

        $this->systemAdapter = $this->framework->getAdapter(System::class);
        $this->inputAdapter = $this->framework->getAdapter(Input::class);
        $this->configAdapter = $this->framework->getAdapter(Config::class);
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        $this->framework->initialize();

        if (!isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) || !\is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl'])) {
            return $this->requestMatcher->matchRequest($request);
        }

        $fragments = null;

        if ($this->configAdapter->get('folderUrl')) {
            try {
                $match = $this->requestMatcher->matchRequest($request);
                $fragments = $this->createFragmentsFromMatch($match);
                $locale = $match['_locale'] ?? '';
            } catch (ResourceNotFoundException $e) {
                // Continue and parse fragments from path
            }
        }

        if (null === $fragments) {
            $pathInfo = $this->parseSuffixAndLanguage($request->getPathInfo(), $locale);
            $fragments = $this->createFragmentsFromPath($pathInfo);
        }

        if ($this->configAdapter->get('addLanguageToUrl')) {
            $this->inputAdapter->setGet('language', $locale);
        }

        $fragments = $this->executeLegacyHook($fragments);
        $pathInfo = $this->createPathFromFragments($fragments, $locale);

        return $this->requestMatcher->matchRequest(Request::create($pathInfo));
    }

    private function createFragmentsFromMatch(array $match): array
    {
        $page = $match['pageModel'] ?? null;
        $parameters = $match['parameters'] ?? '';

        if (!$page instanceof PageModel) {
            throw new ResourceNotFoundException();
        }

        if ($parameters === '') {
            return [$page->alias];
        }

        $fragments = array_merge([$page->alias], explode('/', substr($parameters, 1)));

        // Add the second fragment as auto_item if the number of fragments is even
        if ($this->configAdapter->get('useAutoItem') && 0 === \count($fragments) % 2) {
            array_insert($fragments, 1, array('auto_item'));
        }

        return $fragments;
    }

    private function createFragmentsFromPath(string $pathInfo)
    {
        $fragments = explode('/', substr($pathInfo, 1));

        // Add the second fragment as auto_item if the number of fragments is even
        if ($this->configAdapter->get('useAutoItem') && 0 === \count($fragments) % 2) {
            array_insert($fragments, 1, array('auto_item'));
        }

        return $fragments;
    }

    private function executeLegacyHook(array $fragments)
    {
        foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback) {
            /** @noinspection StaticInvocationViaThisInspection */
            $fragments = $this->systemAdapter->importStatic($callback[0])->{$callback[1]}($fragments);
        }

        // Return if the alias is empty (see #4702 and #4972)
        if ('' === $fragments[0]) {
            throw new ResourceNotFoundException('Page alias is empty.');
        }

        return $fragments;
    }

    private function createPathFromFragments(array $fragments, string $locale)
    {
        if ($this->configAdapter->get('useAutoItem') && $fragments[1] === 'auto_item') {
            unset($fragments[1]);
        }

        $pathInfo = implode('/', $fragments).$this->configAdapter->get('urlSuffix');

        if ($this->configAdapter->get('addLanguageToUrl')) {
            $pathInfo = $locale.'/'.$pathInfo;
        }

        return '/'.$pathInfo;
    }

    private function parseSuffixAndLanguage(string $pathInfo, ?string &$locale)
    {
        $urlSuffix = $this->configAdapter->get('urlSuffix');
        $suffixLength = \strlen($urlSuffix);

        if ($suffixLength !== 0) {
            if (substr($pathInfo, -$suffixLength) !== $urlSuffix) {
                throw new ResourceNotFoundException('URL suffix does not match');
            }

            $pathInfo = substr($pathInfo, 0, -$suffixLength);
        }

        if (0 === strncmp($pathInfo, '/', 1)) {
            $pathInfo = substr($pathInfo, 1);
        }

        if ($this->configAdapter->get('addLanguageToUrl')) {
            $matches = array();

            if (preg_match('@^([a-z]{2}(-[A-Z]{2})?)/(.+)$@', $pathInfo, $matches)) {
                $locale = $matches[1];
                $pathInfo = $matches[3];
            } else {
                throw new ResourceNotFoundException('Locale does not match');
            }
        }

        return $pathInfo;
    }
}
