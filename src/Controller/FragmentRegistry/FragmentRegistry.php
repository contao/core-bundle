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
     * @var array
     */
    private $cache = [];

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
        if (0 !== count($this->cache)) {
            throw new \BadMethodCallException('You cannot add fragments if the fragment registry was already initialized!');
        }

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
    public function getFragmentsByOptionValue($key, $value)
    {
        $cacheKey = md5($key . $value);

        if (isset($this->cache[$cacheKey])) {

            return $this->cache[$cacheKey];
        }

        $matches = [];

        foreach ($this->fragments as $identifier => $fragment) {
            $options = $this->getOptions($identifier);

            if (!isset($options[$key]) || $options[$key] !== $value) {
                continue;
            }

            $matches[$identifier] = $fragment;
        }

        return $this->cache[$cacheKey] = $matches;
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
        foreach ($fragmentRegistry->getFragmentsByOptionValue('fragment', 'contao.page_type') as $identifier => $fragment) {
            $GLOBALS['TL_PTY'][$identifier] = LegacyPageTypeProxy::class;
        }

        // Front end modules
        foreach ($fragmentRegistry->getFragmentsByOptionValue('fragment', 'contao.frontend_module') as $identifier => $fragment) {
            $options = $this->getOptions($identifier);

            if (!isset($options['category'])) {
                continue;
            }

            $GLOBALS['FE_MOD'][$options['category']][$identifier] = LegacyFrontendModuleProxy::class;
        }

        // TODO
        // Content elements
    }
}
