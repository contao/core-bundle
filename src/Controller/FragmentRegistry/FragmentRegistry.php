<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentType\FragmentInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
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
     * @var ControllerReference
     */
    private $controller;

    /**
     * @var array
     */
    private $types;

    /**
     * @var FragmentInterface[]
     */
    private $fragments = [];

    /**
     * @var FragmentInterface[]
     */
    private $fragmentsPerType = [];

    /**
     * @var FragmentInterface[]
     */
    private $fragmentsPerTypeAndName = [];

    /**
     * FragmentRegistry constructor.
     *
     * @param FragmentHandler $fragmentHandler
     * @param string          $controllerName
     */
    public function __construct(FragmentHandler $fragmentHandler, $controllerName)
    {
        $this->fragmentHandler = $fragmentHandler;
        $this->controller = $controllerName;
    }

    /**
     * {@inheritdoc}
     */
    public function addFragmentType($interfaceClassName)
    {
        $this->types[] = $interfaceClassName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragmentTypes()
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function addFragment(FragmentInterface $fragment)
    {
        $this->fragments[] = $fragment;

        foreach ($this->getFragmentTypes() as $type) {
            if (is_a($fragment, $type)) {
                $this->fragmentsPerType[$type][] = $fragment;
                $this->fragmentsPerTypeAndName[$type  .'.' . $fragment->getName()] = $fragment;

                return $this;
            }
        }

        throw new \InvalidArgumentException('No fragment type is responsible for this fragment!');
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
        if (!isset($this->fragmentsPerTypeAndName[$type . '.' . $name])) {
            throw new \InvalidArgumentException('The fragment name "' . $name . '" does not exist for type "' . $type . '"!');
        }

        return $this->fragmentsPerTypeAndName[$type . '.' . $name];
    }

    /**
     * {@inheritdoc}
     */
    public function renderFragment($type, $name, array $configuration, $forceStrategy = null)
    {
        $typeInstance = $this->getFragmentByTypeAndName($type, $name);
        $strategy = $typeInstance->getRenderStrategy($configuration) ?: 'inline';
        $options = $typeInstance->getRenderOptions($configuration) ?: [];
        $query = $typeInstance->getQueryParameters($configuration) ?: [];

        if (null !== $forceStrategy) {
            $strategy = $forceStrategy;
        }

        $uri = new ControllerReference(
            $this->controller, [
                '_type' => $type,
                '_name' => $name,
            ], $query);

        return $this->fragmentHandler->render($uri, $strategy, $options);
    }
}
