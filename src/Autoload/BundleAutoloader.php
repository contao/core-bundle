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
 * FIXME
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
     * @var ConfigCollectionInterface
     */
    protected $collection;

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
        $this->collection  = new ConfigCollection();
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
        // FIXME: UnitTests
        // FIXME: https://github.com/tristanlins/contao-module-core/commit/41e04f5fb269dba1460e3c0222fdea16f1002774
        $bundles    = [];

        $this->addBundlesToCollection($this->findAutoloadFiles(), new JsonParser());
        $this->addBundlesToCollection($this->findLegacyModules(), new IniParser());

        // Make sure the core bundle comes first
        $this->loadingOrder['ContaoCoreBundle'] = [];

        foreach ($this->collection->all() as $bundle) {
            $name = $bundle->getName();

            foreach ($bundle->getReplace() as $package) {
                $this->replace[$package] = $name;
            }

            $this->loadingOrder[$name] = [];

            foreach ($bundle->getLoadAfter() as $package) {
                $this->loadingOrder[$name][] = $package;
            }

            $environments = $bundle->getEnvironments();

            if (in_array($this->getEnvironment(), $environments) || in_array('all', $environments)) {
                $bundleName = $bundle->getName();

                if (!isset($this->loadingOrder[$bundleName])) {
                    $this->loadingOrder[$bundleName] = [];
                }

                $bundles[$bundleName] = $bundle->getClass();
            }
        }

        return $this->order($bundles);
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
     * @param Finder          $files  The finder object
     * @param ParserInterface $parser The parser object
     */
    protected function addBundlesToCollection(Finder $files, ParserInterface $parser)
    {
        $factory = new ConfigFactory();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $configs = $parser->parse($file);

            foreach ($configs['bundles'] as $config) {
                $this->collection->add($factory->create($config));
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
        $loadingOrder = $this->loadingOrder;

        // Handle the replaces
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

        $ordered   = [];
        $available = array_keys($loadingOrder);

        // Try to resolve the loading order
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
                // FIXME: $loadingOrder als Exception-Parameter
                // FIXME: Listener, um die Info optisch aufzubereiten
                // FIXME: print_r('...', true)
                ob_start();
                print_r($loadingOrder);
                $buffer = ob_get_clean();

                throw new UnresolvableLoadingOrderException(
                    "The bundle loading order could not be resolved.\n$buffer"
                );
            }
        }

        $return = [];

        // Sort the bundles
        foreach ($ordered as $package) {
            if (array_key_exists($package, $bundles)) {
                $return[$package] = $bundles[$package];
            }
        }

        return $return;
    }
}
