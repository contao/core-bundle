<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Csrf;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Csrf\MemoryTokenStorage;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class ContaoCsrfTokenManagerTest extends TestCase
{
    public function testGetDefaultTokenValue(): void
    {
        $storage = new MemoryTokenStorage();
        $storage->initialize(['contao_csrf_token' => 'foo']);

        $tokenManager = new ContaoCsrfTokenManager(
            $this->createMock(RequestStack::class),
            'csrf_',
            $this->createMock(TokenGeneratorInterface::class),
            $storage,
            '',
            'contao_csrf_token',
        );

        $token = new CsrfToken('contao_csrf_token', $tokenManager->getDefaultTokenValue());
        $this->assertTrue($tokenManager->isTokenValid($token));

        $secondToken = new CsrfToken('contao_csrf_token', $tokenManager->getDefaultTokenValue());
        $this->assertSame($token->getValue(), $secondToken->getValue());
    }

    public function testGetDefaultTokenValueFailsIfTokenNameIsNotSet(): void
    {
        $tokenManager = new ContaoCsrfTokenManager(
            $this->createMock(RequestStack::class),
            'csrf_',
            $this->createMock(TokenGeneratorInterface::class),
            $this->createMock(TokenStorageInterface::class),
            '',
        );

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The Contao CSRF token manager was not initialized with a default token name.');

        $tokenManager->getDefaultTokenValue();
    }

    public function testCanSkipTokenValidationForEmptySession(): void
    {
        $tokenManager = new ContaoCsrfTokenManager(
            $this->createMock(RequestStack::class),
            'csrf_',
            $this->createMock(TokenGeneratorInterface::class),
            $this->createMock(TokenStorageInterface::class),
            null,
            'contao_csrf_token',
        );

        $session = new Session(new MockArraySessionStorage());
        $session->set('foo', 'bar');
        $session->remove('foo');
        $session->getFlashBag()->set('flash', 'bar');
        $session->getFlashBag()->get('flash');

        $request = Request::create('/');
        $request->setSession($session);

        $this->assertTrue($tokenManager->canSkipTokenValidation($request, 'csrf_contao_csrf_token'));

        $session->set('baz', 'foo');

        $this->assertFalse($tokenManager->canSkipTokenValidation($request, 'csrf_contao_csrf_token'));

        $session->remove('baz');
        $session->getFlashBag()->set('flash', 'baz');

        $this->assertFalse($tokenManager->canSkipTokenValidation($request, 'csrf_contao_csrf_token'));

        $foreignSession = $this->createMock(SessionInterface::class);
        $foreignSession
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $foreignSession
            ->expects($this->once())
            ->method('all')
            ->willReturn([])
        ;

        $request->setSession($foreignSession);

        $this->assertTrue($tokenManager->canSkipTokenValidation($request, 'csrf_contao_csrf_token'));
    }
}
