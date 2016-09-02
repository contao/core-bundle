<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\PreflightRequestEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao preflight route.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PreflightRequestController extends Controller
{
    /**
     * EventDispatcher
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * PreflightController constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handles the preflight request.
     *
     * @return Response
     */
    public function indexAction()
    {
        $event = new PreflightRequestEvent();
        $event->setResponse(new Response());

        $this->eventDispatcher->dispatch(ContaoCoreEvents::PREFLIGHT_REQUEST, $event);

        return $event->getResponse();
    }
}
