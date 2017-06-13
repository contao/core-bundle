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
use Contao\CoreBundle\Routing\ScopeMatcher;
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
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * PageLayoutListener constructor.
     *
     * @param ScopeMatcher             $scopeMatcher
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ScopeMatcher $scopeMatcher, ContaoFrameworkInterface $framework)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->framework = $framework;
    }

    /**
     * @param HeaderReplayEvent $event
     */
    public function onReplay(HeaderReplayEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            return;
        }

        if ($request->cookies->has('TL_VIEW')) {
            $mobile = 'mobile' === $request->cookies->get('TL_VIEW');
        } else {
            $this->framework->initialize();
            $mobile = $this->framework->getAdapter(Environment::class)->get('agent')->mobile;
        }

        $headers = $event->getHeaders();
        $headers->set('Contao-Page-Layout', $mobile ? 'mobile' : 'desktop');
    }
}
