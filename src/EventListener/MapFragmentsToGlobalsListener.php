<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\ContentProxy;
use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistryInterface;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\ModuleProxy;
use Contao\PageProxy;

/**
 * Class MapFragmentsToGlobals.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class MapFragmentsToGlobalsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var FragmentRegistryInterface
     */
    private $fragmentRegistry;

    /**
     * @param FragmentRegistryInterface $fragmentRegistry
     */
    public function __construct(FragmentRegistryInterface $fragmentRegistry)
    {
        $this->fragmentRegistry = $fragmentRegistry;
    }

    /**
     * Maps fragments to old globals.
     */
    public function onInitializeSystem()
    {
        $this->framework->initialize();

        $this->mapPageTypes();
        $this->mapFrontEndModules();
        $this->mapContentElements();
    }

    /**
     * Maps the page types.
     */
    private function mapPageTypes()
    {
        $filter = $this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_PAGE_TYPE);

        foreach ($this->fragmentRegistry->getFragments($filter) as $identifier => $fragment) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            $GLOBALS['TL_PTY'][$options['type']] = PageProxy::class;
        }
    }

    /**
     * Maps the front end modules.
     *
     * @throws \RuntimeException
     */
    private function mapFrontEndModules()
    {
        $filter = $this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE);

        foreach ($this->fragmentRegistry->getFragments($filter) as $identifier => $fragment) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            if (!isset($options['category'])) {
                throw new \RuntimeException(sprintf(
                    'You tagged a fragment as "%s" but forgot to specify the "category" attribute.',
                    FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE
                ));
            }

            $GLOBALS['FE_MOD'][$options['category']][$options['type']] = ModuleProxy::class;
        }
    }

    /**
     * Maps the content elements.
     *
     * @throws \RuntimeException
     */
    private function mapContentElements()
    {
        $filter = $this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT);

        foreach ($this->fragmentRegistry->getFragments($filter) as $identifier => $fragment) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            if (!isset($options['category'])) {
                throw new \RuntimeException(sprintf(
                    'You tagged a fragment as "%s" but forgot to specify the "category" attribute.',
                    FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT
                ));
            }

            $GLOBALS['TL_CTE'][$options['category']][$options['type']] = ContentProxy::class;
        }
    }

    /**
     * Returns the tag filter function.
     *
     * @param string $tag
     *
     * @return \Closure
     */
    private function getTagFilter($tag)
    {
        return function ($identifier) use ($tag) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            if ($options['tag'] !== $tag) {
                return false;
            }

            return true;
        };
    }
}
