<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry;

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
     * @param array $options
     */
    private function ensureBasicOptions(array $options)
    {
        if (3 !== count(array_intersect(array_keys($options), ['tag', 'type', 'controller']))) {
            throw new \InvalidArgumentException('The basic 3 options, tag, type and controller were not provided.');
        }
    }
}
