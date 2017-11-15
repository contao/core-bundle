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
    public function __construct($fragment, array $attributes = array(), array $query = array())
    {
        parent::__construct($fragment, $attributes, $query);

        if (!isset($this->attributes['scope'])) {
            $this->attributes['scope'] = ContaoCoreBundle::SCOPE_FRONTEND;
        }
    }

    public function isFrontend()
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_FRONTEND);
    }

    public function setFrontend()
    {
        $this->attributes['scope'] = ContaoCoreBundle::SCOPE_FRONTEND;
    }

    public function isBackend()
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_BACKEND);
    }

    public function setBackend()
    {
        $this->attributes['scope'] = ContaoCoreBundle::SCOPE_BACKEND;
    }

    private function isScope(string $scope)
    {
        $attributes = $this->attributes;

        return isset($attributes['scope']) && $scope === $attributes['scope'];
    }
}
