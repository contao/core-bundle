<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry;

use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\FrontendModule\LegacyFrontendModuleProxy;
use Contao\CoreBundle\FragmentRegistry\PageType\LegacyPageTypeProxy;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistry implements FragmentRegistryInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

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
        $this->framework->initialize();

        // Page types
        foreach ($this->getFragments($this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_PAGE_TYPE)) as $identifier => $fragment) {
            $options = $this->getOptions($identifier);

            $GLOBALS['TL_PTY'][$options['type']] = LegacyPageTypeProxy::class;
        }

        // Front end modules
        foreach ($this->getFragments($this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE)) as $identifier => $fragment) {
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
        if (3 !== count(array_intersect(array_keys($options), ['tag', 'type', 'controller']))) {
            throw new \InvalidArgumentException('The basic 3 options, tag, type and controller were not provided.');
        }
    }

    /**
     * @param string $tag
     *
     * @return \Closure
     */
    private function getTagFilter($tag)
    {
        return function($identifier) use ($tag) {
            $options = $this->getOptions($identifier);

            if ($options['tag'] !== $tag) {
                return false;
            }

            return true;
        };
    }
}
