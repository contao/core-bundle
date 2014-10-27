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
class BundleFactory implements BundleFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(\SplFileInfo $file, CollectionInterface $collection)
    {
        if (!$file->isFile()) {
            throw new \InvalidArgumentException("$file is not a file");
        }

        $json = json_decode(file_get_contents($file), true);

        if (null === $json) {
            throw new \RuntimeException("File $file cannot be decoded: " . json_last_error_msg());
        }

        if (empty($json['bundles'])) {
            throw new \RuntimeException("No bundles defined in $file");
        }

        foreach ($json['bundles'] as $class => $options) {
            $bundle = new Bundle($this->getClassBasename($class), $class);

            $this->configureBundle($bundle, $options);

            $collection->add($bundle);
        }
    }

    /**
     * Get the bundle name from its class name
     *
     * @param string $class The class name
     *
     * @return string The bundle name
     */
    protected function getClassBasename($class) # FIXME: move
    {
        $chunks = explode('\\', $class);

        return array_pop($chunks);
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
