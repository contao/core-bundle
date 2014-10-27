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
        if (isset($options['replace']) && is_array($options['replace'])) {
            $bundle->setReplace($options['replace']);
        }

        if (isset($options['environments']) && is_array($options['environments'])) {
            $bundle->setEnvironments($options['environments']);
        }

        if (isset($options['load-after']) && is_array($options['load-after'])) {
            $bundle->setLoadAfter($options['load-after']);
        }
    }
}
