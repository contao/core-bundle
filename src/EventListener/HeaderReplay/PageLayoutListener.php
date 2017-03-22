<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\HeaderReplay;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Terminal42\HeaderReplay\Event\HeaderReplayEvent;

/**
 * Extracts the page layout for proper Vary handling based
 * on the terminal42/header-replay-bundle.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PageLayoutListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * PageLayoutListener constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param HeaderReplayEvent $event
     */
    public function onReplay(HeaderReplayEvent $event)
    {
        $request = $event->getRequest();

        $this->framework->initialize();

        $mobile = $this->framework->getAdapter(Environment::class)->get('agent')->mobile;

        if ($request->cookies->has('TL_VIEW')) {
            switch ($request->cookies->get('TL_VIEW')) {
                case 'mobile':
                    $mobile = true;
                    break;
                case 'desktop':
                    $mobile = false;
            }
        }

        $headers = $event->getHeaders();
        $headers->set('Contao-Page-Layout', $mobile ? 'mobile' : 'desktop');
    }
}
