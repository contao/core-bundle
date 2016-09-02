<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\PreflightRequestEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the PreflightRequestEvent class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PreflightRequestEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $event = new PreflightRequestEvent();

        $this->assertInstanceOf('Contao\CoreBundle\Event\PreflightRequestEvent', $event);
    }

    /**
     * Tests the getters and setters.
     */
    public function testSetAndGet()
    {
        $event = new PreflightRequestEvent();
        $response = new Response('foobar');

        $event->setResponse($response);

        $this->assertSame($response, $event->getResponse());
    }
}
