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
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistry implements FragmentRegistryInterface
{
    /**
     * @var FragmentHandler
     */
    private $fragmentHandler;

    /**
     * @var FragmentInterface[]
     */
    private $fragments = [];

    /**
     * @var array
     */
    private $fragmentOptions = [];

    /**
     * FragmentRegistry constructor.
     *
     * @param FragmentHandler $fragmentHandler
     */
    public function __construct(FragmentHandler $fragmentHandler)
    {
        $this->fragmentHandler = $fragmentHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function addFragment($identifier, FragmentInterface $fragment, $options = [])
    {
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
    public function getOptions($identifier)
    {
        return $this->fragmentOptions[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getFragments(callable $filter = null)
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
     * {@inheritdoc}
     */
    public function renderFragment(FragmentInterface $fragment, array $configuration = [], $renderStrategy = 'inline', $renderOptions = [])
    {
        return $this->fragmentHandler->render(
            $fragment->getControllerReference($configuration),
            $renderStrategy,
            $renderOptions
        );
    }

    /**
     * Maps new fragments that were registered properly in the fragment
     * registry to old $GLOBALS arrays for BC.
     */
    public function mapNewFragmentsToLegacyArrays()
    {
        $container = \System::getContainer();

        /** @var \Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface $fragmentRegistry */
        $fragmentRegistry = $container->get('contao.fragment_registry');

        // Page types
        foreach ($fragmentRegistry->getFragments($this->getFragmentFilter('contao.page_type')) as $identifier => $fragment) {
            $GLOBALS['TL_PTY'][$identifier] = LegacyPageTypeProxy::class;
        }

        // Front end modules
        foreach ($fragmentRegistry->getFragments($this->getFragmentFilter('contao.frontend_module')) as $identifier => $fragment) {
            $options = $this->getOptions($identifier);

            if (!isset($options['category'])) {
                continue;
            }

            $GLOBALS['FE_MOD'][$options['category']][$identifier] = LegacyFrontendModuleProxy::class;
        }

        // TODO
        // Content elements
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
