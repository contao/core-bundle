<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener\HeaderReplay;

use Contao\CoreBundle\EventListener\HeaderReplay\BackendSessionListener;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Terminal42\HeaderReplay\Event\HeaderReplayEvent;
use Terminal42\HeaderReplay\EventListener\HeaderReplayListener;

/**
 * Tests the BackendSessionListener class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class BackendSessionListenerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $listener = new BackendSessionListener($this->mockScopeMatcher(), false);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\HeaderReplay\BackendSessionListener', $listener);
    }

    /**
     * Tests no header is added when not in Contao back end scope.
     */
    public function testOnReplayWithNoBackendScope()
    {
        $listener = new BackendSessionListener($this->mockScopeMatcher(), false);

        $request = new Request();
        $headers = new ResponseHeaderBag();
        $event = new HeaderReplayEvent($request, $headers);

        $listener->onReplay($event);

        $this->assertArrayNotHasKey(strtolower(HeaderReplayListener::FORCE_NO_CACHE_HEADER_NAME), $event->getHeaders()->all());
    }

    /**
     * Tests no header is added when the request has no session.
     */
    public function testOnReplayWithNoSession()
    {
        $listener = new BackendSessionListener($this->mockScopeMatcher(), false);

        $request = new Request();
        $request->attributes->set('_scope', 'backend');
        $headers = new ResponseHeaderBag();
        $event = new HeaderReplayEvent($request, $headers);

        $listener->onReplay($event);

        $this->assertArrayNotHasKey(strtolower(HeaderReplayListener::FORCE_NO_CACHE_HEADER_NAME), $event->getHeaders()->all());
    }

    /**
     * Tests no header is added when the request has no back end user authentication
     * cookie.
     */
    public function testOnReplayWithNoAuthCookie()
    {
        $listener = new BackendSessionListener($this->mockScopeMatcher(), false);

        $session = new Session();
        $request = new Request();
        $request->attributes->set('_scope', 'backend');
        $request->setSession($session);
        $headers = new ResponseHeaderBag();
        $event = new HeaderReplayEvent($request, $headers);

        $listener->onReplay($event);

        $this->assertNotNull($request->getSession());
        $this->assertArrayNotHasKey(strtolower(HeaderReplayListener::FORCE_NO_CACHE_HEADER_NAME), $event->getHeaders()->all());
    }

    /**
     * Tests no header is added if the auth cookie has an invalid value.
     */
    public function testOnReplayWithNoValidCookie()
    {
        $listener = new BackendSessionListener($this->mockScopeMatcher(), false);

        $session = new Session();
        $request = new Request();
        $request->attributes->set('_scope', 'backend');
        $request->cookies->set('BE_USER_AUTH', 'foobar');
        $request->setSession($session);
        $headers = new ResponseHeaderBag();
        $event = new HeaderReplayEvent($request, $headers);

        $listener->onReplay($event);

        $this->assertNotNull($request->getSession());
        $this->assertTrue($request->cookies->has('BE_USER_AUTH'));
        $this->assertArrayNotHasKey(strtolower(HeaderReplayListener::FORCE_NO_CACHE_HEADER_NAME), $event->getHeaders()->all());
    }

    /**
     * Tests that the header is correctly added when scope and auth cookie are
     * correct.
     */
    public function testOnReplay()
    {
        $listener = new BackendSessionListener($this->mockScopeMatcher(), false);

        $session = new Session();
        $session->setId('foobar-id');
        $request = new Request();
        $request->attributes->set('_scope', 'backend');
        $request->cookies->set('BE_USER_AUTH', 'f6d5c422c903288859fb5ccf03c8af8b0fb4b70a');
        $request->setSession($session);
        $headers = new ResponseHeaderBag();
        $event = new HeaderReplayEvent($request, $headers);

        $listener->onReplay($event);

        $this->assertNotNull($request->getSession());
        $this->assertTrue($request->cookies->has('BE_USER_AUTH'));
        $this->assertArrayHasKey(strtolower(HeaderReplayListener::FORCE_NO_CACHE_HEADER_NAME), $event->getHeaders()->all());
    }
}
