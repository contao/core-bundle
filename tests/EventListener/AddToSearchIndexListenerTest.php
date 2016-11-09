<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener;

use Contao\CoreBundle\EventListener\AddToSearchIndexListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests the AddToSearchIndexListener class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddToSearchIndexListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContaoFramework|\PHPUnit_Framework_MockObject_MockObject
     */
    private $framework;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->framework = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\ContaoFramework')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $frontendAdapter = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\Adapter')
            ->setMethods(['indexPageIfApplicable'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $frontendAdapter
            ->expects($this->any())
            ->method('indexPageIfApplicable')
            ->willReturn(null)
        ;

        $this->framework
            ->expects($this->any())
            ->method('getAdapter')
            ->willReturn($frontendAdapter)
        ;
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $listener = new AddToSearchIndexListener($this->framework);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\AddToSearchIndexListener', $listener);
    }

    /**
     * Tests that the listener does nothing if the Contao framework is not booted.
     */
    public function testWithoutContaoFramework()
    {
        $this->framework
            ->expects($this->any())
            ->method('isInitialized')
            ->willReturn(false)
        ;

        $listener = new AddToSearchIndexListener($this->framework);
        $event = $this->mockFilterResponseEvent();

        $event
            ->expects($this->never())
            ->method('getResponse')
        ;

        $listener->onKernelResponse($event);
    }

    /**
     * Tests that the listener does use the response if the Contao framework is booted.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWithContaoFramework()
    {
        $this->framework
            ->expects($this->any())
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $listener = new AddToSearchIndexListener($this->framework);
        $event = $this->mockFilterResponseEvent();

        $event
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new Response())
        ;

        $listener->onKernelResponse($event);
    }

    /**
     * Returns a PostResponseEvent mock object.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterResponseEvent
     */
    private function mockFilterResponseEvent()
    {
        return $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterResponseEvent',
            ['getResponse'],
            [
                $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['test', false]),
                new Request(),
                HttpKernelInterface::MASTER_REQUEST,
                new Response(),
            ]
        );
    }
}
