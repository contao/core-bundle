<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

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
     * @var bool
     */
    private $isInitialized = false;

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
        $this->ensureNotInitialized();

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
        $this->ensureNotInitialized();

        $this->fragments[] = $fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragments($type = '')
    {
        $this->initialize();

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
        $this->initialize();

        if (!isset($this->fragmentsPerTypeAndName[$type . '.' . $name])) {
            throw new \InvalidArgumentException('The fragment name "' . $name . '" does not exist for type "' . $type . '"!');
        }

        return $this->fragmentsPerTypeAndName[$type . '.' . $name];
    }

    /**
     * {@inheritdoc}
     */
    public function renderFragment($type, $name, ConfigurationInterface $configuration)
    {
        $typeInstance = $this->getFragmentByTypeAndName($type, $name);
        $typeInstance->modifyConfiguration($configuration);

        $uri = new ControllerReference(
            $this->controller, [
                '_type' => $type,
                '_name' => $name,
            ], $configuration->getQueryParameters()
        );

        return $this->fragmentHandler->render(
            $uri,
            $configuration->getRenderStrategy(),
            $configuration->getRenderOptions()
        );
    }

    /**
     * Makes sure an exception is thrown if the registry was already initialized.
     *
     * @throws \BadMethodCallException
     */
    private function ensureNotInitialized()
    {
        if ($this->isInitialized) {
            throw new \BadMethodCallException('You cannot add types or fragments if the fragment registry was already initialized!');
        }
    }

    /**
     * Initialize fragment types and fragments and fill up cache lookup arrays.
     */
    private function initialize()
    {
        foreach ($this->fragments as $fragment) {
            $ref = new \ReflectionClass($fragment);

            foreach ($this->types as $type) {
                if ($ref->implementsInterface($type)) {
                    $this->fragmentsPerType[$type][] = $fragment;
                    $this->fragmentsPerTypeAndName[$type  .'.' . $fragment->getName()] = $fragment;
                }
            }
        }

        $this->isInitialized = true;
    }
}
