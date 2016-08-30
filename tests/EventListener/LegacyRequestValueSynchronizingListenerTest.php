<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener;

use Contao\CoreBundle\EventListener\LegacyRequestValueSynchronizingListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests the LegacyRequestValueSynchronizingListener class.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class LegacyRequestValueSynchronizingListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the getSubscribedEvents() method subscribes to all desired events.
     */
    public function testSubscribesToAllEvents()
    {
        $this->assertEquals(
            [KernelEvents::REQUEST, KernelEvents::FINISH_REQUEST],
            array_keys(LegacyRequestValueSynchronizingListener::getSubscribedEvents())
        );
    }

    /**
     * Test that the request is handled correctly.
     */
    public function testRequestHandling()
    {
        $stack = new RequestStack();

        $stack->push($request1 = new Request(['param' => 'value']));

        $listener = new LegacyRequestValueSynchronizingListener($stack);
        $listener->startRequest();

        $this->assertEnvironmentHas($request1);
        $this->assertInputHas($request1);

        $stack->push($request2 = new Request(['param2' => 'value2']));
        $listener->startRequest();
        $this->assertEnvironmentHas($request2);
        $this->assertInputHas($request2);
        $listener->finishRequest();
        $stack->pop();
        $this->assertEnvironmentHas($request1);
        $this->assertInputHas($request1);

        $listener->finishRequest();
        $stack->pop();
        $this->assertEnvironmentHas(null);
        $this->assertInputHas(null);
    }

    /**
     * Assertion helper to check that the Environment class holds the correct request.
     *
     * @param Request $expectedRequest The expected request
     */
    private function assertEnvironmentHas($expectedRequest)
    {
        $environment = new \ReflectionProperty('\Contao\Environment', 'objRequest');
        $environment->setAccessible(true);
        $this->assertSame($expectedRequest, $environment->getValue());
    }

    /**
     * Assertion helper to check that the Input class holds the correct request.
     *
     * @param Request $expectedRequest The expected request
     */
    private function assertInputHas($expectedRequest)
    {
        $input = new \ReflectionProperty('\Contao\Input', 'objValueAdapter');
        $input->setAccessible(true);

        if (null === $expectedRequest) {
            $this->assertNull($input->getValue());
            return;
        }

        $this->assertSame($expectedRequest, $input->getValue()->request);
    }
}
