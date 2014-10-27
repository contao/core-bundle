<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\HttpKernel;

use Contao\System;
use Contao\Bundle\CoreBundle\Autoload\ConfigFactory;
use Contao\Bundle\CoreBundle\Autoload\ConfigCollection;
use Contao\Bundle\CoreBundle\Autoload\ConfigCollectionInterface;
use Contao\Bundle\CoreBundle\Autoload\IniParser;
use Contao\Bundle\CoreBundle\Autoload\JsonParser;
use Contao\Bundle\CoreBundle\Autoload\ParserInterface;
use Contao\Bundle\CoreBundle\DependencyInjection\Compiler\AddBundlesToCachePass;
use Contao\Bundle\CoreBundle\Exception\UnresolvableLoadingOrderException;
use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoBundleInterface;
use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoLegacyBundle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Autoloads the bundles
 *
 * @author Leo Feyer <https://contao.org>
 */
abstract class ContaoKernel extends Kernel implements ContaoKernelInterface
{
    /**
     * @var array
     */
    protected $bundlesMap = [];

    /**
     * @var array
     */
    protected $loadingOrder = [];

    /**
     * @var array
     */
    protected $replace = [];

    /**
     * @var array
     */
    protected $contaoBundles = [];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        System::setKernel($this);

        parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function addAutoloadBundles(&$bundles)
    {
        if (empty($this->bundlesMap)) {
            $this->findBundles();
        }

        foreach ($this->bundlesMap as $package => $class) {
            if (null !== $class) {
                $bundles[] = new $class();
            } else {
                $bundles[] = new ContaoLegacyBundle($package, $this->getRootDir());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContaoBundles()
    {
        if (empty($this->contaoBundles)) {
            foreach ($this->getBundles() as $bundle) {
                if ($bundle instanceof ContaoBundleInterface) {
                    $this->contaoBundles[] = $bundle;
                }
            }
        }

        return $this->contaoBundles;
    }

    /**
     * {@inheritdoc}
     */
    public function writeBundleCache()
    {
        file_put_contents(
            $this->getCacheDir() . '/bundles.map',
            sprintf('<?php return %s;', var_export($this->bundlesMap, true))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadBundleCache()
    {
        if (empty($this->bundlesMap) && is_file($this->getCacheDir() . '/bundles.map')) {
            $this->bundlesMap = include $this->getCacheDir() . '/bundles.map';
        }
    }

    /**
     * Finds all bundles to be registered
     *
     * @return array The found bundles
     */
    protected function findBundles()
    {
        // FIXME: UnitTests
        // FIXME: https://github.com/tristanlins/contao-module-core/commit/41e04f5fb269dba1460e3c0222fdea16f1002774
        $bundles    = [];
        $collection = $this->getCollection();

        // Make sure the core bundle comes first
        $this->loadingOrder['ContaoCoreBundle'] = [];

        foreach ($collection->all() as $bundle) {
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

        $this->bundlesMap = $this->order($bundles);
    }

    /**
     * Finds the autoload bundles
     *
     * @return ConfigCollection The autoload bundles collection
     */
    protected function getCollection()
    {
        $collection = new ConfigCollection();

        $this->addBundlesToCollection(
            $collection,
            $this->findJsonConfigs(),
            new JsonParser()
        );

        $this->addBundlesToCollection(
            $collection,
            $this->findIniConfigs(),
            new IniParser()
        );

        return $collection;
    }

    /**
     * Finds Contao autoload bundles
     *
     * @return Finder The finder object
     */
    protected function findJsonConfigs()
    {
        return Finder::create()
            ->files()
            ->name('autoload.json')
            ->in(dirname($this->getRootDir()) . '/vendor')
        ;
    }

    /**
     * Finds Contao legacy bundles
     *
     * @return Finder The finder object
     */
    protected function findIniConfigs()
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
     * @param ConfigCollectionInterface $collection The collection object
     * @param Finder                    $files      The finder object
     * @param ParserInterface           $parser     The parser object
     */
    protected function addBundlesToCollection(ConfigCollectionInterface $collection, Finder $files, ParserInterface $parser)
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

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
    {
        $container = parent::buildContainer();

        $container->addCompilerPass(new AddBundlesToCachePass($this));

        return $container;
    }
}
