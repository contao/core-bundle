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
class Collection
{
    /**
     * @var BundleInterface[]
     */
    protected $bundles = [];

    /**
     * Returns the bundles
     *
     * @return BundleInterface[] The bundles
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Adds a bundle
     *
     * @param BundleInterface $bundle The bundle
     *
     * @return $this The object instance
     */
    public function addBundle(BundleInterface $bundle)
    {
        $this->bundles[] = $bundle;

        return $this;
    }

    /**
     * Adds bundles from a JSON file
     *
     * @param string $file The file path
     *
     * @throws \RuntimeException If the json file does not exists or cannot be decoded
     */
    public function addBundlesFromJsonFile($file)
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("File $file does not exists");
        }

        $json = json_decode(file_get_contents($file), true);

        if (null === $json) {
            throw new \RuntimeException("File $file cannot be decoded: " . json_last_error_msg());
        }

        if (empty($json['bundles'])) {
            throw new \RuntimeException("No bundles defined in $file");
        }

        foreach ($json['bundles'] as $class => $options) {
            $this->addBundle(new Bundle($class, $options));
        }
    }

    /**
     * Adds a Contao legacy bundle
     *
     * @param string $name The module name
     * @param string $path The module path
     *
     * @throws \RuntimeException If the path is invalid
     */
    public function addLegacyBundle($name, $path)
    {
        if (!is_dir($path)) {
            throw new \RuntimeException("Invalid path $path");
        }

        $this->addBundle(new LegacyBundle($name, $this->readAutoloadIni($path)));
    }

    /**
     * Read the autoload.ini if any
     *
     * @param string $path The bundle path
     *
     * @return array The autoload.ini options
     */
	protected function readAutoloadIni($path)
	{
		$options = [];

        if (!file_exists($path . '/config/autoload.ini')) {
	        return $options;
        }

        $config = parse_ini_file($path . '/config/autoload.ini', true);

        if (isset($config['requires'])) {
            $options['load-after'] = $config['requires'];
        }

		if (!isset($config['load-after'])) {
			return $options;
		}

        // Convert optional requirements
        foreach ($options['load-after'] as $k => $v) {
            if (0 === strncmp($v, '*', 1)) {
	            $options['load-after'][$k] = substr($v, 1);
            }
        }

		return $options;
	}
}
