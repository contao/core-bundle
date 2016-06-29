<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\EventListener\MergeHttpHeadersListener;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests the MergeHttpHeadersListenerTest class.
 *
 * @author Yanick Witschi <https:/github.com/toflar>
 */
class MergeHttpHeadersListenerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        /** @var ContaoFrameworkInterface $framework */
        $framework = $this->getMock('Contao\CoreBundle\Framework\ContaoFrameworkInterface');
        $listener = new MergeHttpHeadersListener($framework);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\MergeHttpHeadersListener', $listener);
    }

    /**
     * Tests that the listener is skipped if the framework is not initialized.
     */
    public function testListenerIsSkippedIfFrameworkNotInitialized()
    {
        $responseEvent = new FilterResponseEvent(
            $this->mockKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        /** @var ContaoFrameworkInterface|\PHPUnit_Framework_MockObject_MockObject $framework */
        $framework = $this->getMock('Contao\CoreBundle\Framework\ContaoFrameworkInterface');

        $framework
            ->expects($this->once())
            ->method('isInitialized')
            ->willReturn(false)
        ;

        $listener = new MergeHttpHeadersListener($framework);
        $listener->setHeaders(['FOOBAR: foobar']);
        $listener->onKernelResponse($responseEvent);

        $this->assertFalse($responseEvent->getResponse()->headers->has('FOOBAR'));
    }

    /**
     * Tests that the headers sent using header() are merged into the response object.
     */
    public function testHeadersAreMerged()
    {
        $responseEvent = new FilterResponseEvent(
            $this->mockKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        /** @var ContaoFrameworkInterface|\PHPUnit_Framework_MockObject_MockObject $framework */
        $framework = $this->getMock('Contao\CoreBundle\Framework\ContaoFrameworkInterface');

        $framework
            ->expects($this->once())
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $listener = new MergeHttpHeadersListener($framework);
        $listener->setHeaders(['FOOBAR: content']);
        $listener->onKernelResponse($responseEvent);

        $response = $responseEvent->getResponse();

        $this->assertTrue($response->headers->has('FOOBAR'));
        $this->assertSame('content', $response->headers->get('FOOBAR'));
    }

    /**
     * Tests that existing headers are not overriden.
     */
    public function testHeadersAreNotOverridenIfAlreadyPresentInResponse()
    {
        $response = new Response();
        $response->headers->set('FOOBAR', 'content');

        $responseEvent = new FilterResponseEvent(
            $this->mockKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        /** @var ContaoFrameworkInterface|\PHPUnit_Framework_MockObject_MockObject $framework */
        $framework = $this->getMock('Contao\CoreBundle\Framework\ContaoFrameworkInterface');

        $framework
            ->expects($this->once())
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $listener = new MergeHttpHeadersListener($framework);
        $listener->setHeaders(['FOOBAR: new-content']);
        $listener->onKernelResponse($responseEvent);

        $response = $responseEvent->getResponse();

        $this->assertTrue($response->headers->has('FOOBAR'));
        $this->assertSame('content', $response->headers->get('FOOBAR', null, false)[0]);
    }

    /**
     * Tests that headers are not appended.
     */
    public function testHeadersAreNotAppendedIfAlreadyPresentInResponse()
    {
        $response = new Response();
        $response->headers->set('FOOBAR', 'content');

        $responseEvent = new FilterResponseEvent(
            $this->mockKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        /** @var ContaoFrameworkInterface|\PHPUnit_Framework_MockObject_MockObject $framework */
        $framework = $this->getMock('Contao\CoreBundle\Framework\ContaoFrameworkInterface');

        $framework
            ->expects($this->once())
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $headersCount = count($response->headers->get('FOOBAR', null, false));

        $listener = new MergeHttpHeadersListener($framework);
        $listener->setHeaders(['FOOBAR: new-content']);
        $listener->onKernelResponse($responseEvent);

        $response = $responseEvent->getResponse();

        $this->assertTrue($response->headers->has('FOOBAR'));
        $this->assertSame($headersCount, count($response->headers->get('FOOBAR', null, false)));
    }
}
