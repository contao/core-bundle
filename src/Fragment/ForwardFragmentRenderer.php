<?php

namespace Contao\CoreBundle\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;

class ForwardFragmentRenderer extends InlineFragmentRenderer
{
    /**
     * {@inheritdoc}
     */
    protected function createSubRequest($uri, Request $request)
    {
        return $request->duplicate();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'forward';
    }
}
