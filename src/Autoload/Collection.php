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
 * Handles a collection of autoload bundles
 *
 * @author Leo Feyer <https://contao.org>
 */
class Collection implements CollectionInterface
{
    /**
     * @var BundleInterface[]
     */
    protected $bundles = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function add(BundleInterface $bundle)
    {
        $this->bundles[] = $bundle;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->bundles);
    }
}
