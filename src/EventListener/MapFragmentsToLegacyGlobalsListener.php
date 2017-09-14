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
 * Class MapFragmentsToLegacyGlobals.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class MapFragmentsToLegacyGlobalsListener implements FrameworkAwareInterface
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

        // Page types
        foreach ($this->fragmentRegistry->getFragments(
            $this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_PAGE_TYPE)
        ) as $identifier => $fragment) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            $GLOBALS['TL_PTY'][$options['type']] = PageProxy::class;
        }

        // Front end modules
        foreach ($this->fragmentRegistry->getFragments(
            $this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE)
        ) as $identifier => $fragment) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            if (!isset($options['category'])) {
                throw new \RuntimeException(
                    sprintf('You tagged a fragment as "%s" but forgot to specify the "category" attribute.',
                        FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE
                    )
                );
            }

            $GLOBALS['FE_MOD'][$options['category']][$options['type']] = ModuleProxy::class;
        }

        // Content elements
        foreach ($this->fragmentRegistry->getFragments(
            $this->getTagFilter(FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT)
        ) as $identifier => $fragment) {
            $options = $this->fragmentRegistry->getOptions($identifier);

            if (!isset($options['category'])) {
                throw new \RuntimeException(
                    sprintf('You tagged a fragment as "%s" but forgot to specify the "category" attribute.',
                        FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT
                    )
                );
            }

            $GLOBALS['TL_CTE'][$options['category']][$options['type']] = ContentProxy::class;
        }
    }

    /**
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
