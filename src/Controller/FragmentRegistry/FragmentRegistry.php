<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Contao\CoreBundle\Controller\FrontendModule\LegacyFrontendModuleProxy;
use Contao\CoreBundle\Controller\PageType\LegacyPageTypeProxy;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistry implements FragmentRegistryInterface
{
    /**
     * @var array
     */
    private $fragments = [];

    /**
     * @var array
     */
    private $fragmentOptions = [];

    /**
     * {@inheritdoc}
     */
    public function addFragment(string $identifier, $fragment, array $options): FragmentRegistryInterface
    {
        $this->ensureBasicOptions($options);

        // Overrides existing fragments with same identifier
        $this->fragments[$identifier] = $fragment;
        $this->fragmentOptions[$identifier] = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment($identifier)
    {
        return $this->fragments[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions($identifier): array
    {
        return $this->fragmentOptions[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getFragments(callable $filter = null): array
    {
        $matches = [];

        foreach ($this->fragments as $identifier => $fragment) {
            if (null !== $filter && !$filter($identifier, $fragment)) {
                continue;
            }

            $matches[$identifier] = $fragment;
        }

        return $matches;
    }

    /**
     * Maps new fragments that were registered properly in the fragment
     * registry to old $GLOBALS arrays for BC.
     */
    public function mapNewFragmentsToLegacyArrays()
    {
        // Page types
        foreach ($this->getFragments($this->getFragmentFilter('contao.page_type')) as $identifier => $fragment) {
            $options = $this->getOptions($identifier);

            $GLOBALS['TL_PTY'][$options['type']] = LegacyPageTypeProxy::class;
        }

        // Front end modules
        foreach ($this->getFragments($this->getFragmentFilter('contao.frontend_module')) as $identifier => $fragment) {
            $options = $this->getOptions($identifier);

            if (!isset($options['category'])) {
                throw new \RuntimeException('You tagged a "contao.fragment" as a "contao.frontend_module" but forgot to specify the "category" attribute.');
            }

            $GLOBALS['FE_MOD'][$options['category']][$options['type']] = LegacyFrontendModuleProxy::class;
        }

        // TODO
        // Content elements
    }

    /**
     * @param array $options
     */
    private function ensureBasicOptions(array $options)
    {
        if (0 === count(array_intersect(array_keys($options), ['fragment', 'type', 'controller']))) {
            throw new \InvalidArgumentException('The basic 3 options, fragment, type and controller were not provided.');
        }
    }

    /**
     * @param string $type
     *
     * @return \Closure
     */
    private function getFragmentFilter($type)
    {
        return function($identifier) use ($type) {
            $options = $this->getOptions($identifier);

            if (!isset($options['fragment']) || $options['fragment'] !== $type) {
                return false;
            }

            return true;
        };
    }
}
