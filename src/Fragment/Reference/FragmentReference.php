<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Fragment\Reference;

use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class FragmentReference extends ControllerReference
{
    /**
     * {@inheritdoc}
     */
    public function __construct($fragment, array $attributes = [], array $query = [])
    {
        parent::__construct($fragment, $attributes, $query);

        if (!isset($this->attributes['scope'])) {
            $this->attributes['scope'] = ContaoCoreBundle::SCOPE_FRONTEND;
        }
    }

    public function isFrontend(): bool
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_FRONTEND);
    }

    public function setFrontend(): void
    {
        $this->attributes['scope'] = ContaoCoreBundle::SCOPE_FRONTEND;
    }

    public function isBackend(): bool
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_BACKEND);
    }

    public function setBackend(): void
    {
        $this->attributes['scope'] = ContaoCoreBundle::SCOPE_BACKEND;
    }

    private function isScope(string $scope): bool
    {
        $attributes = $this->attributes;

        return isset($attributes['scope']) && $scope === $attributes['scope'];
    }
}
