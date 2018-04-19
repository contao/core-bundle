<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;

class ForwardFragmentRenderer extends InlineFragmentRenderer
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'forward';
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubRequest($uri, Request $request)
    {
        return $request->duplicate();
    }
}
