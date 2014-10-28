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
     * @var array
     */
    protected $loadingOrder = [];

    /**
     * @var array
     */
    protected $replace = [];

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
     * Finds all bundles to be registered
     *
     * @return array The ordered bundles
     */
    public function load()
    {
        $bundles    = [];
        $collection = $this->createCollection();

        $this->setReplacesFromCollection($collection);
        $this->setLoadingOrderFromCollection($collection);

        foreach ($collection as $config) {
            $environments = $config->getEnvironments();

            if (in_array($this->getEnvironment(), $environments) || in_array('all', $environments)) {
                $bundleName = $config->getName();

                if (!isset($this->loadingOrder[$bundleName])) {
                    $this->loadingOrder[$bundleName] = [];
                }

                $bundles[$bundleName] = $config->getClass();
            }
        }

        return $this->order($bundles);
    }

    /**
     * Creates a configuration collection using autoload.json and legacy autoload.ini files
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
     * Sets the replaces from the collection
     *
     * @param ConfigCollection|ConfigInterface[] $collection The configuration collection
     */
    protected function setReplacesFromCollection(ConfigCollection $collection)
    {
        $this->replace = [];

        foreach ($collection as $bundle) {
            $name = $bundle->getName();

            foreach ($bundle->getReplace() as $package) {
                $this->replace[$package] = $name;
            }
        }
    }

    /**
     * Sets the loading order from the collection
     *
     * @param ConfigCollection|ConfigInterface[] $collection The configuration collection
     */
    protected function setLoadingOrderFromCollection(ConfigCollection $collection)
    {
        $this->loadingOrder = [];

        // Make sure the core bundle comes first
        $this->loadingOrder['ContaoCoreBundle'] = [];

        foreach ($collection as $bundle) {
            $name = $bundle->getName();

            $this->loadingOrder[$name] = [];

            foreach ($bundle->getLoadAfter() as $package) {
                $this->loadingOrder[$name][] = $package;
            }
        }
    }

    /**
     * Order the bundles
     *
     * @param array $bundles The bundles array
     *
     * @return array The resolved bundles array
     *
     * @throws UnresolvableLoadingOrderException If the loading order cannot be resolved
     */
    protected function order(array $bundles)
    {
        $return       = [];
        $loadingOrder = $this->normalizeReplaces($this->loadingOrder);
        $ordered      = $this->resolveLoadingOrder($loadingOrder);

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
     *
     * @return array The normalized loading order array
     */
    protected function normalizeReplaces(array $loadingOrder)
    {
        foreach ($loadingOrder as $k => $v) {
            if (isset($this->replace[$k])) {
                unset($loadingOrder[$k]);
            } else {
                foreach ($v as $kk => $vv) {
                    if (isset($this->replace[$vv])) {
                        $loadingOrder[$k][$kk] = $this->replace[$vv];
                    }
                }
            }
        }

        return $loadingOrder;
    }

    /**
     * Tries to resolve the loading order
     *
     * @param array $loadingOrder
     *
     * @return array
     */
    protected function resolveLoadingOrder(array $loadingOrder)
    {
        $ordered   = [];
        $available = array_keys($loadingOrder);

        while (!empty($loadingOrder)) {
            $failed = true;

            foreach ($loadingOrder as $name => $requires) {
                if (empty($requires)) {
                    $resolved = true;
                } else {
                    $requires = array_intersect($requires, $available);
                    $resolved = (0 === count(array_diff($requires, $ordered)));
                }

                if (true === $resolved) {
                    $ordered[] = $name;
                    unset($loadingOrder[$name]);
                    $failed = false;
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
}
