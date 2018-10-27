<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
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
     * @see \Symfony\Component\HttpKernel\Fragment\FragmentHandler::render()
     */
    public function __construct(string $controller, string $renderer = 'forward', array $options = [])
    {
        $this->controller = $controller;
        $this->renderer = $renderer;
        $this->options = $options;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getRenderer(): string
    {
        return $this->renderer;
    }

    public function setRenderer(string $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    public function setOption(string $name, $option): self
    {
        $this->options[$name] = $option;

        return $this;
    }
}
