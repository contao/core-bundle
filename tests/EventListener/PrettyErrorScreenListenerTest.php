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

use Contao\BackendUser;
use Contao\CoreBundle\EventListener\PrettyErrorScreenListener;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\ForwardPageNotFoundException;
use Contao\CoreBundle\Exception\InsecureInstallationException;
use Contao\CoreBundle\Exception\InsufficientAuthenticationException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\InternalServerErrorHttpException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Fixtures\Exception\PageErrorResponseException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendUser;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PrettyErrorScreenListenerTest extends TestCase
{
    public function testRendersBackEndExceptions(): void
    {
        $exception = new InternalServerErrorHttpException('', new InternalServerErrorException());
        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(BackendUser::class, true);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    public function testDoesNotRenderBackEndExceptionsIfThereIsNoToken(): void
    {
        $twig = $this->createMock('Twig_Environment');
        $framework = $this->mockContaoFramework();
        $scopeMatcher = $this->mockScopeMatcher();

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn(null)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(Kernel::VERSION_ID >= 40100 ? $this->never() : $this->once())
            ->method('critical')
        ;

        $exception = new InternalServerErrorHttpException('', new InternalServerErrorException());
        $event = $this->mockResponseEvent($exception);

        $listener = new PrettyErrorScreenListener(true, $twig, $framework, $tokenStorage, $scopeMatcher, $logger);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    public function testDoesNotRenderBackEndExceptionsIfThereIsNoUser(): void
    {
        $twig = $this->createMock('Twig_Environment');
        $framework = $this->mockContaoFramework();
        $scopeMatcher = $this->mockScopeMatcher();

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn(null)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($token)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(Kernel::VERSION_ID >= 40100 ? $this->never() : $this->once())
            ->method('critical')
        ;

        $exception = new InternalServerErrorHttpException('', new InternalServerErrorException());
        $event = $this->mockResponseEvent($exception);

        $listener = new PrettyErrorScreenListener(true, $twig, $framework, $tokenStorage, $scopeMatcher, $logger);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getErrorTypes
     */
    public function testRendersTheContaoPageHandler(int $type, \Exception $exception): void
    {
        $GLOBALS['TL_PTY']['error_'.$type] = 'Contao\CoreBundle\Fixtures\Controller\PageError'.$type.'Controller';

        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame($type, $event->getResponse()->getStatusCode());

        unset($GLOBALS['TL_PTY']);
    }

    /**
     * @return (UnauthorizedHttpException|AccessDeniedHttpException|NotFoundHttpException|int)[][]
     */
    public function getErrorTypes(): array
    {
        return [
            [401, new UnauthorizedHttpException('', '', new InsufficientAuthenticationException())],
            [403, new AccessDeniedHttpException('', new AccessDeniedException())],
            [404, new NotFoundHttpException('', new PageNotFoundException())],
        ];
    }

    public function testHandlesResponseExceptionsWhenRenderingAPageHandler(): void
    {
        $GLOBALS['TL_PTY']['error_403'] = PageErrorResponseException::class;

        $exception = new AccessDeniedHttpException('', new AccessDeniedException());
        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());

        unset($GLOBALS['TL_PTY']);
    }

    public function testHandlesExceptionsWhenRenderingAPageHandler(): void
    {
        $GLOBALS['TL_PTY']['error_403'] = 'Contao\PageErrorException';

        $exception = new AccessDeniedHttpException('', new AccessDeniedException());
        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertFalse($event->hasResponse());

        unset($GLOBALS['TL_PTY']);
    }

    public function testRendersServiceUnavailableHttpExceptions(): void
    {
        $exception = new ServiceUnavailableHttpException(null, null, new ServiceUnavailableException());
        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(503, $event->getResponse()->getStatusCode());
    }

    public function testDoesNotRenderExceptionsIfDisabled(): void
    {
        $exception = new ServiceUnavailableHttpException(null, null, new ServiceUnavailableException());
        $event = $this->mockResponseEvent($exception);

        $twig = $this->createMock('Twig_Environment');
        $framework = $this->mockContaoFramework();
        $tokenStorage = $this->mockTokenStorage(FrontendUser::class);
        $scopeMatcher = $this->mockScopeMatcher();

        $listener = new PrettyErrorScreenListener(false, $twig, $framework, $tokenStorage, $scopeMatcher);
        $listener->onKernelException($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testDoesNotRenderExceptionsUponSubrequests(): void
    {
        $exception = new ServiceUnavailableHttpException(null, null, new ServiceUnavailableException());
        $event = $this->mockResponseEvent($exception, null, true);

        $twig = $this->createMock('Twig_Environment');
        $framework = $this->mockContaoFramework();
        $tokenStorage = $this->mockTokenStorage(BackendUser::class);

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher
            ->expects($this->never())
            ->method('isContaoRequest')
        ;

        $listener = new PrettyErrorScreenListener(true, $twig, $framework, $tokenStorage, $scopeMatcher);
        $listener->onKernelException($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testRendersUnknownHttpExceptions(): void
    {
        $event = $this->mockResponseEvent(new ConflictHttpException());

        $listener = $this->mockListener(FrontendUser::class, true);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(409, $event->getResponse()->getStatusCode());
    }

    public function testRendersTheErrorScreen(): void
    {
        $exception = new InternalServerErrorHttpException('', new ForwardPageNotFoundException());
        $event = $this->mockResponseEvent($exception);
        $twig = $this->createMock('Twig_Environment');
        $count = 0;

        $twig
            ->method('render')
            ->willReturnCallback(function () use (&$count): void {
                if (0 === $count++) {
                    throw new \Twig_Error('foo');
                }
            })
        ;

        $listener = $this->mockListener(FrontendUser::class, true, $twig);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    public function testDoesNothingIfTheFormatIsNotHtml(): void
    {
        $request = new Request();
        $request->attributes->set('_format', 'json');

        $exception = new InternalServerErrorHttpException('', new InsecureInstallationException());
        $event = $this->mockResponseEvent($exception, $request);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testDoesNothingIfThePageHandlerDoesNotExist(): void
    {
        $exception = new AccessDeniedHttpException('', new AccessDeniedException());
        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testDoesNotLogUnloggableExceptions(): void
    {
        $exception = new InternalServerErrorHttpException('', new InsecureInstallationException());
        $event = $this->mockResponseEvent($exception);

        $listener = $this->mockListener(FrontendUser::class);
        $listener->onKernelException($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    private function mockListener(string $userClass, bool $expectLogging = false, \Twig_Environment $twig = null): PrettyErrorScreenListener
    {
        if (null === $twig) {
            $twig = $this->createMock('Twig_Environment');
        }

        $framework = $this->mockContaoFramework();
        $tokenStorage = $this->mockTokenStorage($userClass);
        $scopeMatcher = $this->mockScopeMatcher();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(
                ($expectLogging && Kernel::VERSION_ID < 40100)
                    ? $this->once()
                    : $this->never()
            )
            ->method('critical')
        ;

        return new PrettyErrorScreenListener(true, $twig, $framework, $tokenStorage, $scopeMatcher, $logger);
    }

    private function mockResponseEvent(\Exception $exception, Request $request = null, bool $isSubRequest = false): GetResponseForExceptionEvent
    {
        $kernel = $this->createMock(KernelInterface::class);

        if (null === $request) {
            $request = new Request();
            $request->attributes->set('_scope', 'backend');
        }

        $type = $isSubRequest ? HttpKernelInterface::SUB_REQUEST : HttpKernelInterface::MASTER_REQUEST;

        return new GetResponseForExceptionEvent($kernel, $request, $type, $exception);
    }
}
