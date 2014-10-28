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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Finds the autoload bundles
 *
 * @author Leo Feyer <https://contao.org>
 */
class BundleAutoloader
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $environment;

    /**
     * Constructor
     *
     * @param string $rootDir     The kernel root directory
     * @param string $environment The current environment
     */
    public function __construct($rootDir, $environment)
    {
        $this->rootDir     = $rootDir;
        $this->environment = $environment;
    }

    /**
     * Returns an ordered bundle collection
     *
     * @return ConfigCollection|ConfigCollection[] The ordered bundles
     */
    public function load()
    {
        $collection = new ConfigCollection($this->environment);

        $this->addBundlesToCollection($collection, $this->findAutoloadFiles(), new JsonParser());
        $this->addBundlesToCollection($collection, $this->findLegacyModules(), new IniParser());

        return $collection->all();
    }

    /**
     * Finds the autoload.json files
     *
     * @return Finder The finder object
     */
    protected function findAutoloadFiles()
    {
        return Finder::create()
            ->files()
            ->name('autoload.json')
            ->in(dirname($this->rootDir) . '/vendor')
        ;
    }

    /**
     * Finds the Contao legacy modules
     *
     * @return Finder The finder object
     */
    protected function findLegacyModules()
    {
        return Finder::create()
            ->directories()
            ->depth('== 0')
            ->ignoreDotFiles(true)
            ->sortByName()
            ->in(dirname($this->rootDir) . '/system/modules')
        ;
    }

    /**
     * Adds bundles to the collection
     *
     * @param ConfigCollection $collection The configuration collection
     * @param Finder           $files      The finder object
     * @param ParserInterface  $parser     The parser object
     */
    protected function addBundlesToCollection(ConfigCollection $collection, Finder $files, ParserInterface $parser)
    {
        $factory = new ConfigFactory();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $configs = $parser->parse($file);

            foreach ($configs['bundles'] as $config) {
                $collection->add($factory->create($config));
            }
        }
    }
}
