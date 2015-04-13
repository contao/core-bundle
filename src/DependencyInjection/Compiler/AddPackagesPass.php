<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Composer\VersionParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds the composer packages and versions to the container.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddPackagesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $jsonFile;

    /**
     * @var VersionParser
     */
    private $parser;

    /**
     * Constructor.
     *
     * @param string        $jsonFile Path to the composer installed.json file
     * @param VersionParser $parser   The Composer version parser.
     */
    public function __construct($jsonFile, VersionParser $parser)
    {
        $this->jsonFile = $jsonFile;
        $this->parser   = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $packages = [];

        if (is_file($this->jsonFile)) {
            $json = json_decode(file_get_contents($this->jsonFile), true);

            if (null !== $json) {
                $packages = $this->getVersions($json);
            }
        }

        $container->setParameter('kernel.packages', $packages);
        $container->setParameter('kernel.normalized_packages', $this->normalizeVersions($packages));
    }

    /**
     * Extracts the version numbers from the JSON data.
     *
     * @param array $json The JSON array
     *
     * @return array The packages array
     */
    private function getVersions(array $json)
    {
        $packages = [];

        foreach ($json as $package) {
            $this->addVersion($package, $packages);
        }

        return $packages;
    }

    /**
     * Adds a version to the packages array.
     *
     * @param array $package  The package
     * @param array $packages The packages array
     */
    private function addVersion(array $package, array &$packages)
    {
        $version = $package['version'];

        if (isset($package['extra']['branch-alias'][$package['version']])) {
            $version = $package['extra']['branch-alias'][$package['version']];
        }

        $packages[$package['name']] = $version;
    }

    /**
     * Adds a normalized version to the packages array.
     *
     * @param array $packages The packages array
     *
     * @return array The normalized packages array
     */
    private function normalizeVersions(array $packages)
    {
        foreach ($packages as $name => $version) {
            $packages[$name] = $this->parser->normalize($version);
        }

        return $packages;
    }
}
