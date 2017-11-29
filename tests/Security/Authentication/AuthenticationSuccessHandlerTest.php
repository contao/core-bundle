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

use Contao\BackendUser;
use Contao\CoreBundle\Event\PostAuthenticateEvent;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\Authentication\AuthenticationSuccessHandler;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendUser;
use Contao\PageModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Tests the AuthenticationSuccessHandler class.
 */
class AuthenticationSuccessHandlerTest extends TestCase
{
    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        unset($GLOBALS['TL_HOOKS']);

        $this->httpUtils = $this->createMock(HttpUtils::class);
        $this->framework = $this->mockContaoFramework();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->mockRouter();
        $this->mockEventDispatcher(false);

        $handler = new AuthenticationSuccessHandler($this->httpUtils, [], $this->framework, $this->router, $this->eventDispatcher);

        $this->assertInstanceOf('Contao\CoreBundle\Security\Authentication\AuthenticationSuccessHandler', $handler);
    }

    /**
     * Tests if redirects to target referer when set.
     */
    public function testRedirectsToTargetRefererWhenSet(): void
    {
        $this->mockRouter();
        $this->mockRequest(['_target_referer' => 'foobar']);
        $this->mockToken();
        $this->mockEventDispatcher(false);

        $handler = new AuthenticationSuccessHandler($this->httpUtils, [], $this->framework, $this->router, $this->eventDispatcher);
        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * Tests if redirects to default target path if no valid user is given.
     */
    public function testRedirectsToDefaultTargetPathIfNoValidUserIsGiven(): void
    {
        $this->mockRouter();
        $this->mockRequest();
        $this->mockToken();
        $this->mockEventDispatcher(false);

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * Tests the redirect response for BackendUser on login route.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testHandleBackendUserOnLoginRoute(): void
    {
        $this->mockRouter();
        $this->mockRequest([], ['_route' => 'contao_backend_login']);
        $this->mockToken(BackendUser::class);
        $this->mockEventDispatcher(true);

        $this->httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($this->request, 'foobar')
            ->willReturn(new RedirectResponse('foobar'))
        ;

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * Tests the execution of the postAuthenticate hook with a BackendUser.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testExecutesThePostAuthenticateHookWithABackendUser(): void
    {
        $this->framework
            ->expects($this->once())
            ->method('createInstance')
            ->willReturn($this)
        ;

        $GLOBALS['TL_HOOKS'] = [
            'postAuthenticate' => [[\get_class($this), 'executePostAuthenticateHookWithABackendUserCallback']],
        ];

        $this->mockRouter();
        $this->mockRequest([], ['_route' => 'contao_backend_login']);
        $this->mockToken(BackendUser::class);
        $this->mockEventDispatcher(true);

        $this->httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($this->request, 'foobar')
            ->willReturn(new RedirectResponse('foobar'))
        ;

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * Tests the execution of the postAuthenticate hook with a FrontendUser.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testExecutesThePostAuthenticateHookWithAFrontendUser(): void
    {
        $this->framework
            ->expects($this->once())
            ->method('createInstance')
            ->willReturn($this)
        ;

        $GLOBALS['TL_HOOKS'] = [
            'postAuthenticate' => [[\get_class($this), 'executePostAuthenticateHookWithAFrontendUserCallback']],
        ];

        $this->mockRouter();
        $this->mockRequest([], ['_route' => 'contao_backend_login']);
        $this->mockToken(FrontendUser::class);
        $this->mockEventDispatcher(true);

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * postAuthenticate hook stub for BackendUser.
     *
     * @param BackendUser $user
     */
    public static function executePostAuthenticateHookWithABackendUserCallback(BackendUser $user): void
    {
        self::assertInstanceOf('Contao\BackendUser', $user);
    }

    /**
     * postAuthenticate hook stub for FrontendUser.
     *
     * @param FrontendUser $user
     */
    public static function executePostAuthenticateHookWithAFrontendUserCallback(FrontendUser $user): void
    {
        self::assertInstanceOf('Contao\FrontendUser', $user);
    }

    /**
     * Tests the redirect response for BackendUser without last page visited.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testHandleBackendUserWithoutLastPageVisited(): void
    {
        $this->mockRouter('contao_backend_login', [], '/contao');
        $this->mockRequest([], ['_route' => 'contao_root']);
        $this->mockToken(BackendUser::class);
        $this->mockEventDispatcher(true);

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', '/contao'));
    }

    /**
     * Tests the redirect response for BackendUser with last page visited.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testHandleBackendUserWithLastPageVisited(): void
    {
        $this->mockRouter('contao_backend_login', ['referer' => 'L2NvbnRhbz9kbz1mb29iYXI='], '/contao?do=foobar');
        $this->mockRequest([], ['_route' => 'contao_backend'], ['do' => 'foobar']);
        $this->mockToken(BackendUser::class);
        $this->mockEventDispatcher(true);

        $this->request->server->set('REQUEST_URI', '/contao?do=foobar');

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', '/contao?do=foobar'));
    }

    /**
     * Tests the redirect response for FrontentUser without groups set.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testHandleFrontendUserWithoutGroups(): void
    {
        $this->mockRouter();
        $this->mockRequest();
        $this->mockToken(FrontendUser::class);
        $this->mockEventDispatcher(true);

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * Tests the redirect response for FrontentUser with groups set but invalid group page.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testHandleFrontendUserWithGroupsButInvalidGroupPage(): void
    {
        $this->mockRouter();
        $this->mockRequest();
        $this->mockToken(FrontendUser::class, [1, 2, 3]);
        $this->mockEventDispatcher(true);

        $adapter = $this->mockPageModelAdapter('findFirstActiveByMemberGroups', [1, 2, 3], null);
        $this->framework = $this->mockContaoFramework([PageModel::class => $adapter]);

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'foobar'));
    }

    /**
     * Tests the redirect response for FrontentUser with groups set and valid group page.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the postAuthenticate hook has been deprecated %s.
     */
    public function testHandleFrontendUserWithGroupsAndValidGroupPage(): void
    {
        $this->mockRouter();
        $this->mockRequest();
        $this->mockToken(FrontendUser::class, [1, 2, 3]);
        $this->mockEventDispatcher(true);

        $page = $this->createMock(PageModel::class);
        $page
            ->expects($this->once())
            ->method('getAbsoluteUrl')
            ->willReturn('group-page')
        ;

        $adapter = $this->mockPageModelAdapter('findFirstActiveByMemberGroups', [1, 2, 3], $page);
        $this->framework = $this->mockContaoFramework([PageModel::class => $adapter]);

        $handler = new AuthenticationSuccessHandler(
            $this->httpUtils,
            ['default_target_path' => 'foobar'],
            $this->framework,
            $this->router,
            $this->eventDispatcher
        );

        $response = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertTrue($response->headers->contains('location', 'group-page'));
    }

    /**
     * Mocks the request with options, attributes and query parameters.
     *
     * @param array $options
     * @param array $attributes
     * @param array $query
     */
    private function mockRequest(array $options = [], array $attributes = [], $query = []): void
    {
        $this->request = new Request();

        foreach ($options as $key => $value) {
            $this->request->request->set($key, $value);
        }

        foreach ($attributes as $key => $value) {
            $this->request->attributes->set($key, $value);
        }

        foreach ($query as $key => $value) {
            $this->request->query->set($key, $value);
        }
    }

    /**
     * Mocks a Token with an optional user class object.
     *
     * @param string     $class
     * @param array|null $groups
     */
    private function mockToken($class = null, array $groups = null): void
    {
        $this->token = $this->createMock(TokenInterface::class);

        if (null !== $class) {
            $this->mockUser($class, $groups);

            $this->token
                ->expects($this->once())
                ->method('getUser')
                ->willReturn($this->user)
            ;
        }
    }

    /**
     * Mocks the User with an optional username.
     *
     * @param string     $class
     * @param array|null $groups
     */
    private function mockUser(string $class = 'Contao\User', array $groups = null): void
    {
        $this->user = $this->createPartialMock($class, ['getUsername']);

        if (null !== $groups) {
            $this->user->groups = serialize($groups);
        }
    }

    /**
     * Mocks the router with a route, parameters and a return value.
     *
     * @param string|null $route
     * @param array       $parameters
     * @param null        $return
     */
    private function mockRouter(string $route = null, array $parameters = [], $return = null): void
    {
        $this->router = $this->createMock(RouterInterface::class);

        if (null !== $route) {
            $this->router
                ->expects($this->once())
                ->method('generate')
                ->with($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL)
                ->willReturn($return)
            ;
        }
    }

    /**
     * Mocks a PageModelAdapter with a method, parameters and a return value.
     *
     * @param string|null $method
     * @param null        $with
     * @param null        $willReturn
     *
     * @return Adapter
     */
    private function mockPageModelAdapter(string $method = null, $with = null, $willReturn = null): Adapter
    {
        $adapter = $this->mockAdapter([$method]);
        $adapter
            ->expects($this->once())
            ->method($method)
            ->with($with)
            ->willReturn($willReturn)
        ;

        return $adapter;
    }

    /**
     * Mocks the event dispatcher.
     *
     * @param bool $expectsDispatchEvent
     */
    private function mockEventDispatcher(bool $expectsDispatchEvent): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        if (true === $expectsDispatchEvent) {
            $this->eventDispatcher
                ->expects($this->once())
                ->method('dispatch')
                ->with(PostAuthenticateEvent::NAME)
            ;
        }

        if (false === $expectsDispatchEvent) {
            $this->eventDispatcher
                ->expects($this->never())
                ->method('dispatch')
            ;
        }
    }
}
