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

/**
 * Resolves the bundles map from the configurations object
 *
 * @author Leo Feyer <https://contao.org>
 */
class ConfigResolver
{
    /**
     * @var ConfigInterface[]
     */
    protected $configs = [];

    /**
     * Adds a configuration object to the collection
     *
     * @param ConfigInterface $config
     *
     * @return $this The collection object
     */
    public function add(ConfigInterface $config)
    {
        $this->configs[] = $config;

        return $this;
    }

    /**
     * Returns a bundles map for an environment
     *
     * @param string $environment The environment
     *
     * @return array The bundles map
     */
    public function getBundlesMapForEnvironment($environment)
    {
        $bundles      = [];
        $replaces     = $this->getReplaces();
        $loadingOrder = $this->getLoadingOrder();

        foreach ($this->configs as $config) {
            if ($this->matchesEnvironment($config->getEnvironments(), $environment)) {
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
     * Gets the replaces from the configuration objects
     *
     * @return array The replaces array
     */
    protected function getReplaces()
    {
        $replace = [];

        foreach ($this->configs as $bundle) {
            $name = $bundle->getName();

            foreach ($bundle->getReplace() as $package) {
                $replace[$package] = $name;
            }
        }

        return $replace;
    }

    /**
     * Gets the loading order from the configuration objects
     *
     * @return array The loading order array
     */
    protected function getLoadingOrder()
    {
        // Make sure the core bundle comes first
        $loadingOrder = [
            'ContaoCoreBundle' => []
        ];

        foreach ($this->configs as $bundle) {
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
     * @param array  $environments The bundle environments
     * @param string $environment  The current environment
     *
     * @return bool True if the environment matches the bundle environments
     */
    protected function matchesEnvironment(array $environments, $environment)
    {
        return in_array($environment, $environments) || in_array('all', $environments);
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
