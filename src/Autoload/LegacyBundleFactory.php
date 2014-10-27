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
 * Bundle factory
 *
 * @author Leo Feyer <https://contao.org>
 */
class LegacyBundleFactory implements BundleFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(\SplFileInfo $dir, CollectionInterface $collection)
    {
        if (!$dir->isDir()) {
            throw new \InvalidArgumentException("$dir is not a directory");
        }

        $bundle = new Bundle($dir->getBasename(), null);

        if (file_exists($dir . '/config/autoload.ini')) {
            $options = parse_ini_file($dir . '/config/autoload.ini', true);

            if (false === $options) {
                $error = error_get_last();

                throw new \RuntimeException("File $dir/config/autoload.ini cannot be decoded: " . $error['message']);
            }

            $this->configureBundle($bundle, $options);
        }

        $collection->add($bundle);
    }

    /**
     * Configures the bundle
     *
     * @param BundleInterface $bundle  The bundle object
     * @param array           $options The options array
     */
    protected function configureBundle(BundleInterface $bundle, array $options)
    {
        if (!isset($options['requires']) || !is_array($options['requires'])) {
            return;
        }

        // Convert optional requirements
        foreach ($options['requires'] as $k => $v) {
            if (0 === strncmp($v, '*', 1)) {
                $options['requires'][$k] = substr($v, 1);
            }
        }

        $bundle->setLoadAfter($options['requires']);
    }
}
