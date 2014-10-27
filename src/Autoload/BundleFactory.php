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

/**
 * Creates bundles and adds them to a collection
 *
 * @author Leo Feyer <https://contao.org>
 */
class BundleFactory implements BundleFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $config, CollectionInterface $collection)
    {
        foreach ($config['bundles'] as $options) {
            $bundle = new Bundle($options['name'], $options['class']);

            $this->configureBundle($bundle, $options);

            $collection->add($bundle);
        }
    }

    /**
     * Configures the bundle
     *
     * @param BundleInterface $bundle  The bundle object
     * @param array           $options The options array
     */
    protected function configureBundle(BundleInterface $bundle, array $options)
    {
        if ($this->hasReplace($options)) {
            $bundle->setReplace($options['replace']);
        }

        if ($this->hasEnvironments($options)) {
            $bundle->setEnvironments($options['environments']);
        }

        if ($this->hasLoadAfter($options)) {
            $bundle->setLoadAfter($options['load-after']);
        }
    }

    /**
     * Checks whether there is a "replace" section
     *
     * @param array $options The options array
     *
     * @return bool True if there is a "replace" section
     */
    protected function hasReplace(array $options)
    {
        return isset($options['replace']) && is_array($options['replace']);
    }

    /**
     * Checks whether there is an "environments" section
     *
     * @param array $options The options array
     *
     * @return bool True if there is an "environments" section
     */
    protected function hasEnvironments(array $options)
    {
        return isset($options['environments']) && is_array($options['environments']);
    }

    /**
     * Checks whether there is a "load-after" section
     *
     * @param array $options The options array
     *
     * @return bool True if there is a "load-after" section
     */
    protected function hasLoadAfter(array $options)
    {
        return isset($options['load-after']) && is_array($options['load-after']);
    }
}
