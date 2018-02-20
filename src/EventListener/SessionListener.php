<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\EventListener\SessionListener as BaseSessionListener;

/**
 * Decorates the default session listener.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class SessionListener implements EventSubscriberInterface
{
    /**
     * @var BaseSessionListener
     */
    private $inner;

    private $framework;

    private $scopeMatcher;

    /**
     * Constructor.
     *
     * @param BaseSessionListener      $inner
     * @param ContaoFrameworkInterface $framework
     * @param ScopeMatcher             $scopeMatcher
     */
    public function __construct(BaseSessionListener $inner, ContaoFrameworkInterface $framework, ScopeMatcher $scopeMatcher)
    {
        $this->inner = $inner;
        $this->framework = $framework;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        return $this->inner->onKernelRequest($event);
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->framework->isInitialized() || !$this->scopeMatcher->isFrontendMasterRequest($event)) {
            $this->inner->onKernelResponse($event);

            return;
        }

        $session = $event->getRequest()->getSession();

        if ($session && $session->isStarted()) {
            $session->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return AbstractSessionListener::getSubscribedEvents();
    }
}
