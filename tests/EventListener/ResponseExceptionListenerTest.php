<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\CoreBundle\EventListener\ResponseExceptionListener;
use Contao\CoreBundle\Exception\ResponseException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ResponseExceptionListenerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $listener = new ResponseExceptionListener();

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\ResponseExceptionListener', $listener);
    }

    public function testAddsAResponseToTheEvent(): void
    {
        $event = $this->mockResponseEvent(new ResponseException(new Response('Foo')));

        $listener = new ResponseExceptionListener();
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertTrue($event->isAllowingCustomResponseCode());

        $response = $event->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Foo', $response->getContent());
    }

    public function testDoesNotAddAResponseToTheEventIfTheExceptionIsNotAResponseException(): void
    {
        $event = $this->mockResponseEvent(new \RuntimeException());

        $listener = new ResponseExceptionListener();
        $listener->onKernelException($event);

        $this->assertFalse($event->hasResponse());
        $this->assertFalse($event->isAllowingCustomResponseCode());
    }

    /**
     * Mocks a response event.
     *
     * @param \Exception $exception
     *
     * @return GetResponseForExceptionEvent
     */
    private function mockResponseEvent(\Exception $exception): GetResponseForExceptionEvent
    {
        $kernel = $this->createMock(KernelInterface::class);
        $request = new Request();

        return new GetResponseForExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);
    }
}
