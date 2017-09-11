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
 * RenderStrategy.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class RenderStrategy
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
     * RenderStrategy constructor.
     *
     * @param string $renderStrategy
     * @param array  $renderOptions
     */
    public function __construct($renderStrategy = 'inline', array $renderOptions = [])
    {
        $this->renderStrategy = $renderStrategy;
        $this->renderOptions  = $renderOptions;
    }

    /**
     * @param string $renderStrategy
     *
     * @return RenderStrategy
     */
    public function setRenderStrategy($renderStrategy)
    {
        $this->renderStrategy = (string) $renderStrategy;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderStrategy($configuration = null)
    {
        return $this->renderStrategy;
    }

    /**
     * @param array $renderOptions
     *
     * @return RenderStrategy
     */
    public function setRenderOptions(array $renderOptions)
    {
        $this->renderOptions = $renderOptions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderOptions($configuration = null)
    {
        return $this->renderOptions;
    }
}
