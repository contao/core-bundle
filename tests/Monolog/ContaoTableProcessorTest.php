<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Monolog;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Monolog\ContaoTableProcessor;
use Contao\CoreBundle\Tests\TestCase;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ContaoTableProcessorTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $processor = $this->mockContaoTableProcessor();

        $this->assertInstanceOf('Contao\CoreBundle\Monolog\ContaoTableProcessor', $processor);
    }

    public function testCanBeInvoked(): void
    {
        $processor = $this->mockContaoTableProcessor();

        $this->assertEmpty($processor([]));
        $this->assertSame(['foo' => 'bar'], $processor(['foo' => 'bar']));
        $this->assertSame(['context' => ['contao' => false]], $processor(['context' => ['contao' => false]]));
    }

    /**
     * @param int    $logLevel
     * @param string $expectedAction
     *
     * @dataProvider actionLevelProvider
     */
    public function testReturnsDifferentActionsForDifferentErrorLevels(int $logLevel, string $expectedAction): void
    {
        $data = [
            'level' => $logLevel,
            'context' => ['contao' => new ContaoContext(__METHOD__)],
        ];

        $processor = $this->mockContaoTableProcessor();
        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame($expectedAction, $context->getAction());
    }

    /**
     * @param int $logLevel
     *
     * @dataProvider actionLevelProvider
     */
    public function testDoesNotChangeAnExistingAction(int $logLevel): void
    {
        $data = [
            'level' => $logLevel,
            'context' => ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)],
        ];

        $processor = $this->mockContaoTableProcessor();
        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame(ContaoContext::CRON, $context->getAction());
    }

    /**
     * @return array
     */
    public function actionLevelProvider(): array
    {
        return [
            [Logger::DEBUG, ContaoContext::GENERAL],
            [Logger::INFO, ContaoContext::GENERAL],
            [Logger::NOTICE, ContaoContext::GENERAL],
            [Logger::WARNING, ContaoContext::GENERAL],
            [Logger::ERROR, ContaoContext::ERROR],
            [Logger::CRITICAL, ContaoContext::ERROR],
            [Logger::ALERT, ContaoContext::ERROR],
            [Logger::EMERGENCY, ContaoContext::ERROR],
        ];
    }

    public function testAddsTheUserAgent(): void
    {
        $request = new Request([], [], [], [], [], ['HTTP_USER_AGENT' => 'Contao test']);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $processor = $this->mockContaoTableProcessor($requestStack);

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__, null, null, null, 'foobar'),
            ],
        ];

        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame('foobar', $context->getBrowser());

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__),
            ],
        ];

        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame('Contao test', $context->getBrowser());

        $requestStack->pop();

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__),
            ],
        ];

        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame('N/A', $context->getBrowser());
    }

    public function testAddsTheUsername(): void
    {
        $token = $this->createMock(UsernamePasswordToken::class);

        $token
            ->method('getUsername')
            ->willReturn('k.jones')
        ;

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $processor = $this->mockContaoTableProcessor(null, $tokenStorage);

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__, null, 'foobar'),
            ],
        ];

        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame('foobar', $context->getUsername());

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__),
            ],
        ];

        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame('k.jones', $context->getUsername());

        $tokenStorage->setToken();

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__),
            ],
        ];

        $record = $processor($data);

        /** @var ContaoContext $context */
        $context = $record['extra']['contao'];

        $this->assertSame('N/A', $context->getUsername());
    }

    /**
     * @param string|null $scope
     * @param string|null $contextSource
     * @param string      $expectedSource
     *
     * @dataProvider sourceProvider
     */
    public function testAddsTheSource(?string $scope, ?string $contextSource, string $expectedSource): void
    {
        $requestStack = new RequestStack();

        if (null !== $scope) {
            $request = new Request();
            $request->attributes->set('_scope', $scope);

            $requestStack->push($request);
        }

        $data = [
            'context' => [
                'contao' => new ContaoContext(__METHOD__, null, null, null, null, $contextSource),
            ],
        ];

        $processor = $this->mockContaoTableProcessor($requestStack);
        $result = $processor($data);

        /** @var ContaoContext $context */
        $context = $result['extra']['contao'];

        $this->assertSame($expectedSource, $context->getSource());
    }

    /**
     * @return array
     */
    public function sourceProvider(): array
    {
        return [
            [null, 'FE', 'FE'],
            [null, 'BE', 'BE'],
            [null, null, 'FE'],

            [ContaoCoreBundle::SCOPE_FRONTEND, 'FE', 'FE'],
            [ContaoCoreBundle::SCOPE_FRONTEND, 'BE', 'BE'],
            [ContaoCoreBundle::SCOPE_FRONTEND, null, 'FE'],

            [ContaoCoreBundle::SCOPE_BACKEND, 'FE', 'FE'],
            [ContaoCoreBundle::SCOPE_BACKEND, 'BE', 'BE'],
            [ContaoCoreBundle::SCOPE_BACKEND, null, 'BE'],
        ];
    }

    /**
     * Mocks a Contao table processor.
     *
     * @param RequestStack|null          $requestStack
     * @param TokenStorageInterface|null $tokenStorage
     *
     * @return ContaoTableProcessor
     */
    private function mockContaoTableProcessor(RequestStack $requestStack = null, TokenStorageInterface $tokenStorage = null): ContaoTableProcessor
    {
        if (null === $requestStack) {
            $requestStack = $this->createMock(RequestStack::class);
        }

        if (null === $tokenStorage) {
            $tokenStorage = $this->createMock(TokenStorageInterface::class);
        }

        return new ContaoTableProcessor($requestStack, $tokenStorage, $this->mockScopeMatcher());
    }
}
