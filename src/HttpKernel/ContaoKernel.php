<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\HttpKernel;

use Contao\System;
use Contao\CoreBundle\Autoload\BundleAutoloader;
use Contao\CoreBundle\DependencyInjection\Compiler\AddBundlesToCachePass;
use Contao\CoreBundle\HttpKernel\Bundle\ContaoBundleInterface;
use Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Custom Contao kernel
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
            $this->bundlesMap = $this->generateBundlesMap();
        }

        foreach ($this->bundlesMap as $package => $class) {
            $bundles[] = $this->createBundleInstance($package, $class);
        }
    }

    /**
     * Create a bundle from the class information provided.
     *
     * @param string      $package The package name.
     *
     * @param string|null $class   The class name.
     *
     * @return ContaoModuleBundle|BundleInterface
     */
    protected function createBundleInstance($package, $class)
    {
        if (null === $class) {
            return new ContaoModuleBundle($package, $this->getRootDir());
        }

        return new $class();
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
     * Generates the bundles map
     *
     * @return array The bundles map
     */
    protected function generateBundlesMap()
    {
        $autoloader = new BundleAutoloader($this->getRootDir(), $this->getEnvironment());

        return $autoloader->load();
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
