<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistry implements FragmentRegistryInterface
{
    const TYPES = [
        ContentElementInterface::class,
        FrontendModuleInterface::class,
        InsertTagInterface::class,
        PageTypeInterface::class,
    ];

    /**
     * @var array
     */
    private $fragments = [];

    /**
     * @var array
     */
    private $fragmentsPerType = [];

    /**
     * FragmentRegistry constructor.
     */
    public function __construct()
    {
        foreach (self::TYPES as $type) {
            $this->fragmentsPerType[$type] = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFragment(FragmentInterface $fragment)
    {
        $this->fragments[] = $fragment;

        foreach (self::TYPES as $type) {
            if (is_a($fragment, $type)) {
                $this->fragmentsPerType[$type][] = $fragment;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFragments($type = '')
    {
        if ('' === $type) {
            return $this->fragments;
        }

        if (!isset($this->fragmentsPerType[$type])) {
            throw new \InvalidArgumentException('The fragment type "' . $type . '" does not exist!');
        }

        return $this->fragmentsPerType[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function getFragmentByTypeAndName($type, $name)
    {
        foreach ($this->getFragments($type) as $fragment) {
            if ($name === $fragment->getName()) {
                return $fragment;
            }
        }

        throw new \InvalidArgumentException('The fragment name "' . $name . '" does not exist for type "' . $type . '"!');
    }
}
