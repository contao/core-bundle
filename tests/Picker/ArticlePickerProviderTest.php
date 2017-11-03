<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Picker;

use Contao\BackendUser;
use Contao\CoreBundle\Picker\ArticlePickerProvider;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\TestCase\ContaoTestCase;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArticlePickerProviderTest extends ContaoTestCase
{
    /**
     * @var ArticlePickerProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $GLOBALS['TL_LANG']['MSC']['articlePicker'] = 'Article picker';
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unset($GLOBALS['TL_LANG']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $menuFactory = $this->createMock(FactoryInterface::class);

        $menuFactory
            ->method('createItem')
            ->willReturnArgument(1)
        ;

        $router = $this->createMock(RouterInterface::class);

        $router
            ->method('generate')
            ->willReturnCallback(
                function (string $name, array $params): string {
                    return $name.'?'.http_build_query($params);
                }
            )
        ;

        $this->provider = new ArticlePickerProvider($menuFactory, $router);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Picker\ArticlePickerProvider', $this->provider);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Using a picker provider without injecting the translator service has been deprecated %s.
     */
    public function testCreatesTheMenuItem(): void
    {
        $picker = json_encode([
            'context' => 'link',
            'extras' => [],
            'current' => 'articlePicker',
            'value' => '',
        ]);

        if (\function_exists('gzencode') && false !== ($encoded = @gzencode($picker))) {
            $picker = $encoded;
        }

        $this->assertSame(
            [
                'label' => 'Article picker',
                'linkAttributes' => ['class' => 'articlePicker'],
                'current' => true,
                'uri' => 'contao_backend?do=article&popup=1&picker='.strtr(base64_encode($picker), '+/=', '-_,'),
            ],
            $this->provider->createMenuItem(new PickerConfig('link', [], '', 'articlePicker'))
        );
    }

    public function testChecksIfAMenuItemIsCurrent(): void
    {
        $this->assertTrue($this->provider->isCurrent(new PickerConfig('link', [], '', 'articlePicker')));
        $this->assertFalse($this->provider->isCurrent(new PickerConfig('link', [], '', 'filePicker')));
    }

    public function testReturnsTheCorrectName(): void
    {
        $this->assertSame('articlePicker', $this->provider->getName());
    }

    public function testChecksIfAContextIsSupported(): void
    {
        $this->provider->setTokenStorage($this->mockTokenStorage(BackendUser::class));

        $this->assertTrue($this->provider->supportsContext('link'));
        $this->assertFalse($this->provider->supportsContext('file'));
    }

    public function testFailsToCheckTheContextIfThereIsNoTokenStorage(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No token storage provided');

        $this->provider->supportsContext('link');
    }

    public function testFailsToCheckTheContextIfThereIsNoToken(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $tokenStorage
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->provider->setTokenStorage($tokenStorage);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No token provided');

        $this->provider->supportsContext('link');
    }

    public function testFailsToCheckTheContextIfThereIsNoUser(): void
    {
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

        $this->provider->setTokenStorage($tokenStorage);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The token does not contain a back end user object');

        $this->provider->supportsContext('link');
    }

    public function testChecksIfAValueIsSupported(): void
    {
        $this->assertTrue($this->provider->supportsValue(new PickerConfig('link', [], '{{article_url::5}}')));
        $this->assertFalse($this->provider->supportsValue(new PickerConfig('link', [], '{{link_url::5}}')));
    }

    public function testReturnsTheDcaTable(): void
    {
        $this->assertSame('tl_article', $this->provider->getDcaTable());
    }

    public function testReturnsTheDcaAttributes(): void
    {
        $this->assertSame(
            [
                'fieldType' => 'radio',
                'value' => '5',
            ],
            $this->provider->getDcaAttributes(new PickerConfig('link', [], '{{article_url::5}}'))
        );

        $this->assertSame(
            ['fieldType' => 'radio'],
            $this->provider->getDcaAttributes(new PickerConfig('link', [], '{{link_url::5}}'))
        );
    }

    public function testConvertsTheDcaValue(): void
    {
        $this->assertSame('{{article_url::5}}', $this->provider->convertDcaValue(new PickerConfig('link'), 5));
    }
}
