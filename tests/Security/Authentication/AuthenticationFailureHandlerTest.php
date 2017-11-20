<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Security\Authentication;

use Contao\CoreBundle\Security\Authentication\AuthenticationFailureHandler;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Tests the AuthenticationFailureHandler class.
 */
class AuthenticationFailureHandlerTest extends TestCase
{
    protected $session;
    protected $scopeMatcher;
    protected $translator;
    protected $flashBag;
    protected $httpKernel;
    protected $httpUtils;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->httpKernel = $this->createMock(HttpKernel::class);
        $this->httpUtils = $this->createMock(HttpUtils::class);

        $this->session = $this->mockSession();
        $this->scopeMatcher = $this->mockScopeMatcher();
        $this->mockTranslator();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $handler = $this->mockFailureHandler();

        $this->assertInstanceOf('Contao\CoreBundle\Security\Authentication\AuthenticationFailureHandler', $handler);
    }

    /**
     * Tests the redirect to referer on frontend authentication failure.
     */
    public function testRedirectsToRefererOnFrontendAuthenticationFailure(): void
    {
        $this->mockTranslator(
            true,
            'ERR.invalidLogin',
            [],
            'contao_default',
            'Login failed (note that usernames and passwords are case-sensitive)!'
        );

        $request = $this->mockRequest(
            ['_scope' => 'frontend'],
            ['referer' => '/']
        );

        $this->session = $this->mockSession();

        $request->setSession($this->session);
        $authenticationException = new AuthenticationException();

        $handler = $this->mockFailureHandler();
        $response = $handler->onAuthenticationFailure($request, $authenticationException);

        $authenticationError = $request->getSession()->get('_security.last_error');

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getFlashBag();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertInstanceOf('Symfony\Component\Security\Core\Exception\AuthenticationException', $authenticationError);
        $this->assertTrue($response->headers->contains('location', '/'));
        $this->assertSame('Login failed (note that usernames and passwords are case-sensitive)!', $flashBag->get('contao.FE.error')[0]);
    }

    /**
     * Tests the redirect to referer on frontend authentication failure.
     */
    public function testRedirectsToRequestUriOnBackendAuthenticationFailure(): void
    {
        $this->mockTranslator(
            true,
            'ERR.invalidLogin',
            [],
            'contao_default',
            'Login failed (note that usernames and passwords are case-sensitive)!'
        );

        $request = $this->mockRequest(
            ['_scope' => 'backend']
        );

        $this->session = $this->mockSession();

        $request->setSession($this->session);
        $authenticationException = new AuthenticationException();

        $handler = $this->mockFailureHandler();
        $response = $handler->onAuthenticationFailure($request, $authenticationException);

        $authenticationError = $request->getSession()->get('_security.last_error');

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getFlashBag();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertInstanceOf('Symfony\Component\Security\Core\Exception\AuthenticationException', $authenticationError);
        $this->assertTrue($response->headers->contains('location', '/contao/login'));
        $this->assertSame('Login failed (note that usernames and passwords are case-sensitive)!', $flashBag->get('contao.BE.error')[0]);
    }

    /**
     * Mocks the request with options, attributes and query parameters.
     *
     * @param array $attributes
     * @param array $headers
     *
     * @return Request
     */
    private function mockRequest(array $attributes = [], array $headers = []): Request
    {
        $request = Request::create('http://localhost/contao/login');

        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        foreach ($attributes as $key => $value) {
            $request->attributes->set($key, $value);
        }

        return $request;
    }

    /**
     * Mocks a translator with an optional translation.
     *
     * @param bool   $withTranslation
     * @param string $key
     * @param array  $params
     * @param string $domain
     * @param string $translated
     */
    private function mockTranslator(bool $withTranslation = false, string $key = '', array $params = [], string $domain = 'contao_default', string $translated = ''): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        if (true === $withTranslation) {
            $this->translator
                ->expects($this->once())
                ->method('trans')
                ->with($key, $params, $domain)
                ->willReturn($translated)
            ;
        }
    }

    /**
     * Mocks a AuthenticationFailureHandler.
     *
     * @return AuthenticationFailureHandler
     */
    private function mockFailureHandler(): AuthenticationFailureHandler
    {
        return new AuthenticationFailureHandler(
            $this->httpKernel,
            $this->httpUtils,
            [],
            null,
            $this->scopeMatcher,
            $this->translator
        );
    }
}
