<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Controller;
use Contao\CoreBundle\Controller\PreflightRequestController;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\PreflightRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;


/**
 * Tests the PreflightRequestController class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PreflightRequestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $controller = new PreflightRequestController(new EventDispatcher());

        $this->assertInstanceOf('Contao\CoreBundle\Controller\PreflightRequestController', $controller);
    }

    /**
     * Tests the controller indexAction.
     */
    public function testIndexAction()
    {
        $response = new Response();
        $event = new PreflightRequestEvent();
        $event->setResponse($response);
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(ContaoCoreEvents::PREFLIGHT_REQUEST, function(PreflightRequestEvent $event) {
            $event->getResponse()->setContent('foobar content');

        });
        $controller = new PreflightRequestController($eventDispatcher);

        $this->assertSame('foobar content', $controller->indexAction()->getContent());
    }
}
