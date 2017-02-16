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
 * Base configuration class for fragment configuration.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $renderStrategy = 'inline';

    /**
     * @var array
     */
    private $renderOptions = [];

    /**
     * @var array
     */
    private $queryParameters = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * {@inheritdoc}
     */
    public function setRenderStrategy($renderStrategy)
    {
        $this->renderStrategy = $renderStrategy;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderStrategy()
    {
        return $this->renderStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenderOptions($renderOptions)
    {
        $this->renderOptions = $renderOptions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderOptions()
    {
        return $this->renderOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryParameters($queryParameters)
    {
        $this->queryParameters = $queryParameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
