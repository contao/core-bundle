<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Autoload;

use Contao\Bundle\CoreBundle\Exception\UnresolvableLoadingOrderException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Finds the autoload bundles and orders them
 *
 * @author Leo Feyer <https://contao.org>
 */
class BundleAutoloader
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $environment;

    /**
     * Constructor
     *
     * @param string $rootDir     The kernel root directory
     * @param string $environment The current environment
     */
    public function __construct($rootDir, $environment)
    {
        $this->rootDir     = $rootDir;
        $this->environment = $environment;
    }

    /**
     * Returns the root directory
     *
     * @return string The root directory
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * Returns the environment
     *
     * @return string The environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns an ordered array of bundles to register
     *
     * @return array The ordered bundles
     */
    public function load()
    {
        $bundles      = [];
        $collection   = $this->createCollection();
        $replaces     = $this->getReplacesFromCollection($collection);
        $loadingOrder = $this->getLoadingOrderFromCollection($collection);

        foreach ($collection as $config) {
            if ($this->matchesEnvironment($config->getEnvironments())) {
                $bundleName = $config->getName();

                if (!isset($loadingOrder[$bundleName])) {
                    $loadingOrder[$bundleName] = [];
                }

                $bundles[$bundleName] = $config->getClass();
            }
        }

        $normalizedOrder = $this->normalizeLoadingOrder($loadingOrder, $replaces);
        $resolvedOrder   = $this->resolveLoadingOrder($normalizedOrder);

        return $this->order($bundles, $resolvedOrder);

    }

    /**
     * Creates a configuration collection
     *
     * @return ConfigCollection|ConfigInterface[] The configuration collection
     */
    protected function createCollection()
    {
        $collection = new ConfigCollection();

        $this->addBundlesToCollection($collection, $this->findAutoloadFiles(), new JsonParser());
        $this->addBundlesToCollection($collection, $this->findLegacyModules(), new IniParser());

        return $collection;
    }

    /**
     * Finds the autoload.json files
     *
     * @return Finder The finder object
     */
    protected function findAutoloadFiles()
    {
        return Finder::create()
            ->files()
            ->name('autoload.json')
            ->in(dirname($this->getRootDir()) . '/vendor')
        ;
    }

    /**
     * Finds the Contao legacy modules
     *
     * @return Finder The finder object
     */
    protected function findLegacyModules()
    {
        return Finder::create()
            ->directories()
            ->depth('== 0')
            ->ignoreDotFiles(true)
            ->sortByName()
            ->in(dirname($this->getRootDir()) . '/system/modules')
        ;
    }

    /**
     * Adds bundles to the collection
     *
     * @param ConfigCollection $collection The configuration collection
     * @param Finder           $files      The finder object
     * @param ParserInterface  $parser     The parser object
     */
    protected function addBundlesToCollection(ConfigCollection $collection, Finder $files, ParserInterface $parser)
    {
        $factory = new ConfigFactory();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $configs = $parser->parse($file);

            foreach ($configs['bundles'] as $config) {
                $collection->add($factory->create($config));
            }
        }
    }

    /**
     * Gets the replaces from the collection
     *
     * @param ConfigCollection|ConfigInterface[] $collection The configuration collection
     *
     * @return array The replaces array
     */
    protected function getReplacesFromCollection(ConfigCollection $collection)
    {
        $replace = [];

        foreach ($collection as $bundle) {
            $name = $bundle->getName();

            foreach ($bundle->getReplace() as $package) {
                $replace[$package] = $name;
            }
        }

        return $replace;
    }

    /**
     * Gets the loading order from the collection
     *
     * @param ConfigCollection|ConfigInterface[] $collection The configuration collection
     *
     * @return array The loading order array
     */
    protected function getLoadingOrderFromCollection(ConfigCollection $collection)
    {
        // Make sure the core bundle comes first
        $loadingOrder = [
            'ContaoCoreBundle' => []
        ];

        foreach ($collection as $bundle) {
            $name = $bundle->getName();

            $loadingOrder[$name] = [];

            foreach ($bundle->getLoadAfter() as $package) {
                $loadingOrder[$name][] = $package;
            }
        }

        return $loadingOrder;
    }

    /**
     * Checks whether a bundle should be loaded in an environment
     *
     * @param array $environments The bundle environments
     *
     * @return bool True if the environment matches the bundle environments
     */
    protected function matchesEnvironment(array $environments)
    {
        return in_array($this->getEnvironment(), $environments) || in_array('all', $environments);
    }

    /**
     * Orders the bundles in the resolved loading order
     *
     * @param array $bundles The bundles array
     * @param array $ordered The resolved loading order array
     *
     * @return array The ordered bundles array
     */
    protected function order(array $bundles, array $ordered)
    {
        $return  = [];

        foreach ($ordered as $package) {
            if (array_key_exists($package, $bundles)) {
                $return[$package] = $bundles[$package];
            }
        }

        return $return;
    }

    /**
     * Normalizes the replaces
     *
     * @param array $loadingOrder The loading order array
     * @param array $replace      The replaces array
     *
     * @return array The normalized loading order array
     */
    protected function normalizeLoadingOrder(array $loadingOrder, array $replace)
    {
        foreach ($loadingOrder as $bundleName => &$loadAfter) {
            if (isset($replace[$bundleName])) {
                unset($loadingOrder[$bundleName]);
            } else {
                $this->replaceBundleNames($loadAfter, $replace);
            }
        }

        return $loadingOrder;
    }

    /**
     * Replaces the legacy bundle names with their new name
     *
     * @param array $loadAfter The load-after array
     * @param array $replace   The replaces array
     */
    protected function replaceBundleNames(array &$loadAfter, array $replace)
    {
        foreach ($loadAfter as &$bundleName) {
            if (isset($replace[$bundleName])) {
                $bundleName = $replace[$bundleName];
            }
        }
    }

    /**
     * Tries to resolve the loading order
     *
     * @param array $loadingOrder The normalized loading order array
     *
     * @return array The resolved loading order array
     *
     * @throws UnresolvableLoadingOrderException If the loading order cannot be resolved
     */
    protected function resolveLoadingOrder(array $loadingOrder)
    {
        $ordered   = [];
        $available = array_keys($loadingOrder);

        while (!empty($loadingOrder)) {
            $failed = true;

            foreach ($loadingOrder as $name => $requires) {
                if (true === $this->canBeResolved($requires, $available, $ordered)) {
                    $failed    = false;
                    $ordered[] = $name;

                    unset($loadingOrder[$name]);
                }
            }

            if (true === $failed) {
                throw new UnresolvableLoadingOrderException(
                    "The bundle loading order could not be resolved.\n" . print_r($loadingOrder, true)
                );
            }
        }

        return $ordered;
    }

    /**
     * Checks whether the requirements of a bundle can be resolved
     *
     * @param array $requires  The requirements array
     * @param array $available The installed bundle names
     * @param array $ordered   The normalized order array
     *
     * @return bool True if the requirements can be resolved
     */
    protected function canBeResolved(array $requires, array $available, array $ordered)
    {
        if (empty($requires)) {
            return true;
        }

        return (0 === count(array_diff(array_intersect($requires, $available), $ordered)));
    }
}
