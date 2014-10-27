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

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Creates bundles and adds them to a collection
 *
 * @author Leo Feyer <https://contao.org>
 */
class BundleFactory implements BundleFactoryInterface
{
    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

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
        $bundle->setReplace($this->accessor->getValue($options, '[replace]') ?: []);
        $bundle->setEnvironments($this->accessor->getValue($options, '[environments]') ?: []);
        $bundle->setLoadAfter($this->accessor->getValue($options, '[load-after]') ?: []);
    }
}
