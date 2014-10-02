<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\HttpKernel;

use Contao\System;
use Contao\Bundle\CoreBundle\DependencyInjection\Compiler\AddBundlesToCachePass;
use Contao\Bundle\CoreBundle\Exception\UnresolvableDependenciesException;
use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoBundleInterface;
use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoLegacyBundle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\Bundle;
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
    protected $resolved = [];

    /**
     * @var array
     */
    protected $dependencies = [];

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
    public function registerBundles()
    {
        if (empty($this->resolved)) {
            $this->findBundles();
        }

        $bundles = [];

        foreach ($this->resolved as $package) {
            if ('Bundle' === substr($package, -6)) {
                $bundles[] = new $package();
            } else {
                $bundles[] = new ContaoLegacyBundle($package);
            }
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function writeBundleCache()
    {
        file_put_contents(
            $this->getCacheDir() . '/bundles.map',
            sprintf('<?php return %s;', var_export($this->resolved, true))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadBundleCache()
    {
        if (empty($this->resolved) && is_file($this->getCacheDir() . '/bundles.map')) {
            $this->resolved = include $this->getCacheDir() . '/bundles.map';
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
     * Finds all bundles to be registered
     *
     * @return array The found bundles
     */
    protected function findBundles()
    {
        $bundles = [];

        $this->findContaoBundles($bundles);
        $this->findLegacyBundles($bundles);

        $this->resolved = $this->resolveDependencies($bundles);
    }

    /**
     * Finds all Contao bundles
     *
     * @param array $bundles The bundles array
     */
    protected function findContaoBundles(array &$bundles)
    {
        $vendor = realpath(__DIR__ . '/../../../../');
        $files  = Finder::create()->files()->depth('== 2')->name('autoload.json')->in($vendor);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $json = json_decode(file_get_contents($file->getPathname()), true);

            if (null === $json) {
                continue;
            }

            if (empty($json['bundles'])) {
                throw new \RuntimeException('No bundles defined in ' . $file->getRelativePathname() . '.');
            }

            $classes = array_keys($json['bundles']);
            $bundle  = new $classes[0]();

            if (!$bundle instanceof Bundle) {
                throw new \RuntimeException($classes[0] . ' is not a bundle.');
            }

            if (false === strpos($bundle->getPath(), $file->getPath())) {
                throw new \RuntimeException('The first registered bundle should be the bundle itself.');
            }

            $name = $bundle->getName();

            if (!empty($json['replace'])) {
                foreach ($json['replace'] as $package) {
                    $this->replace[$package] = $name;
                }
            }

            $this->dependencies[$name] = [];

            if (!empty($json['require'])) {
                foreach ($json['require'] as $package) {
                    $this->dependencies[$name][] = $package;
                }
            }

            if (!empty($json['require-if-exists'])) {
                foreach ($json['require-if-exists'] as $package) {
                    $this->dependencies[$name][] = '*' . $package;
                }
            }

            foreach ($json['bundles'] as $class => $options) {
                if (
                    !isset($options['environment'])
                    || in_array($this->getEnvironment(), $options['environment'])
                    || in_array('all', $options['environment'])
                ) {
                    $bundle = new $class();

                    if (!$bundle instanceof Bundle) {
                        throw new \RuntimeException($class . ' is not a bundle.');
                    }

                    $bundleName = $bundle->getName();

                    if (!isset($this->dependencies[$bundleName])) {
                        $this->dependencies[$bundleName] = [];
                    }

                    $bundles[$bundleName] = $class;
                }
            }
        }
    }

    /**
     * Finds all legacy bundles
     *
     * @param array $bundles The bundles array
     */
    protected function findLegacyBundles(array &$bundles)
    {
        $dir     = realpath(__DIR__ . '/../../../../../system/modules');
        $modules = Finder::create()->directories()->depth('== 0')->in($dir)->ignoreDotFiles(true)->sortByName();

        /** @var \SplFileInfo $module */
        foreach ($modules as $module) {
            $name   = $module->getBasename();
            $legacy = ['backend', 'frontend', 'rep_base', 'rep_client', 'registration', 'rss_reader', 'tpl_editor'];

            // Ignore legacy modules
            if (in_array($name, $legacy)) {
                continue;
            }

            // Ignore disabled module
            if (file_exists($module->getPathname() . '/.skip')) {
                continue;
            }

            $this->dependencies[$name] = [];

            // Read the autoload.ini if any
            if (file_exists($module->getPathname() . '/config/autoload.ini')) {
                $config = parse_ini_file($module->getPathname() . '/config/autoload.ini', true);

                if (isset($config['requires'])) {
                    $this->dependencies[$name] = $config['requires'];
                }
            }

            $bundles[$name] = $name;
        }
    }

    /**
     * Resolves the dependencies
     *
     * @param array $bundles The bundles array
     *
     * @return array The resolved bundles array
     */
    protected function resolveDependencies(array $bundles)
    {
        $dependencies = $this->dependencies;

        // Handle the replaces
        foreach ($dependencies as $k => $v) {
            if (isset($this->replace[$k])) {
                unset($dependencies[$k]);
            } else {
                foreach ($v as $kk => $vv) {
                    if (0 === strncmp($vv, '*', 1)) {
                        $key = substr($vv, 1);

                        if (isset($this->replace[$key])) {
                            $dependencies[$k][$kk] = '*' . $this->replace[$key];
                        }
                    } else {
                        if (isset($this->replace[$vv])) {
                            $dependencies[$k][$kk] = $this->replace[$vv];
                        }
                    }
                }
            }
        }

        $available = array_keys($dependencies);

        // Handle the optional requirements
        foreach ($dependencies as $k => $v) {
            foreach ($v as $kk => $vv) {
                if (0 === strncmp($vv, '*', 1)) {
                    $key = substr($vv, 1);

                    if (!in_array($key, $available)) {
                        unset($dependencies[$k][$kk]);
                    } else {
                        $dependencies[$k][$kk] = $key;
                    }
                }
            }
        }

        $ordered = [];

        // Resolve the dependencies
        while (!empty($dependencies)) {
            $failed = true;

            foreach ($dependencies as $name => $requires) {
                if (empty($requires)) {
                    $resolved = true;
                } else {
                    $resolved = count(array_diff($requires, $ordered)) === 0;
                }

                if (true === $resolved) {
                    $ordered[] = $name;
                    unset($dependencies[$name]);
                    $failed = false;
                }
            }

            // The dependencies cannot be resolved
            if (true === $failed) {
                ob_start();
                print_r($dependencies);
                $buffer = ob_get_clean();

                throw new UnresolvableDependenciesException("The module dependencies could not be resolved.\n$buffer");
            }
        }

        $return = [];

        // Sort the bundles
        foreach ($ordered as $package) {
            if (isset($bundles[$package])) {
                $return[] = $bundles[$package];
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
