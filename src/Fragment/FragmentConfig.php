<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Fragment;

class FragmentConfig
{
    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $renderer;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param string $controller
     * @param string $renderer
     * @param array  $options
     *
     * @see \Symfony\Component\HttpKernel\Fragment\FragmentHandler::render()
     */
    public function __construct(string $controller, string $renderer = 'inline', array $options = [])
    {
        $this->controller = $controller;
        $this->renderer = $renderer;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return FragmentConfig
     */
    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getRenderer(): string
    {
        return $this->renderer;
    }

    /**
     * @param string $renderer
     *
     * @return FragmentConfig
     */
    public function setRenderer(string $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return FragmentConfig
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $option
     *
     * @return FragmentConfig
     */
    public function setOption(string $name, $option): self
    {
        $this->options[$name] = $option;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }
}
