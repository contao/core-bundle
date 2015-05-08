<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Terminal42\ContaoAdapterBundle\Adapter\FrontendAdapter;

/**
 * Adds a page to the search index after the response has been sent.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddToSearchIndexListener
{
    /**
     * @var FrontendAdapter
     */
    private $frontend;

    /**
     * Constructor.
     *
     * @param FrontendAdapter $frontend
     */
    public function __construct(FrontendAdapter $frontend)
    {
        $this->frontend = $frontend;
    }


    /**
     * Forwards the request to the Frontend class if there is a page object.
     *
     * @param PostResponseEvent $event The event object
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        // FIXME: should be replaced with "Response implements Indexable" (see https://github.com/contao/symfony-todo/issues/3)
        if (!defined('TL_ROOT')) {
            return;
        }

        $this->frontend->indexPageIfApplicable($event->getResponse());
    }
}
