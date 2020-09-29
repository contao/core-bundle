<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Picker;

use Contao\BackendUser;
use Contao\CoreBundle\Picker\ArticlePickerProvider;
use Contao\CoreBundle\Picker\PickerConfig;
use Knp\Menu\FactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Tests the ArticlePickerProvider class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ArticlePickerProviderTest extends TestCase
{
    /**
     * @var ArticlePickerProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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
                function ($name, array $params) {
                    return $name.'?'.http_build_query($params);
                }
            )
        ;

        $this->provider = new ArticlePickerProvider($menuFactory, $router);

        $GLOBALS['TL_LANG']['MSC']['articlePicker'] = 'Article picker';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($GLOBALS['TL_LANG']);
    }

    /**
     * Tests the createMenuItem() method.
     */
    public function testCreatesTheMenuItem()
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

    /**
     * Tests the isCurrent() method.
     */
    public function testChecksIfAMenuItemIsCurrent()
    {
        $this->assertTrue($this->provider->isCurrent(new PickerConfig('link', [], '', 'articlePicker')));
        $this->assertFalse($this->provider->isCurrent(new PickerConfig('link', [], '', 'filePicker')));
    }

    /**
     * Tests the getName() method.
     */
    public function testReturnsTheCorrectName()
    {
        $this->assertSame('articlePicker', $this->provider->getName());
    }

    /**
     * Tests the supportsContext() method.
     */
    public function testChecksIfAContextIsSupported()
    {
        $user = $this
            ->getMockBuilder(BackendUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasAccess'])
            ->getMock()
        ;

        $user
            ->method('hasAccess')
            ->willReturn(true)
        ;

        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $tokenStorage
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->provider->setTokenStorage($tokenStorage);

        $this->assertTrue($this->provider->supportsContext('link'));
        $this->assertFalse($this->provider->supportsContext('file'));
    }

    /**
     * Tests the supportsContext() method without token storage.
     */
    public function testFailsToCheckTheContextIfThereIsNoTokenStorage()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No token storage provided');

        $this->provider->supportsContext('link');
    }

    /**
     * Tests the supportsContext() method without token.
     */
    public function testFailsToCheckTheContextIfThereIsNoToken()
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

    /**
     * Tests the supportsContext() method without a user object.
     */
    public function testFailsToCheckTheContextIfThereIsNoUser()
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

    /**
     * Tests the supportsValue() method.
     */
    public function testChecksIfAValueIsSupported()
    {
        $this->assertTrue($this->provider->supportsValue(new PickerConfig('link', [], '{{article_url::5}}')));
        $this->assertFalse($this->provider->supportsValue(new PickerConfig('link', [], '{{link_url::5}}')));
    }

    /**
     * Tests the getDcaTable() method.
     */
    public function testReturnsTheDcaTable()
    {
        $this->assertSame('tl_article', $this->provider->getDcaTable());
    }

    /**
     * Tests the getDcaAttributes() method.
     */
    public function testReturnsTheDcaAttributes()
    {
        $extra = ['source' => 'tl_article.2'];

        $this->assertSame(
            [
                'fieldType' => 'radio',
                'preserveRecord' => 'tl_article.2',
                'value' => '5',
            ],
            $this->provider->getDcaAttributes(new PickerConfig('link', $extra, '{{article_url::5}}'))
        );

        $this->assertSame(
            [
                'fieldType' => 'radio',
                'preserveRecord' => 'tl_article.2',
            ],
            $this->provider->getDcaAttributes(new PickerConfig('link', $extra, '{{link_url::5}}'))
        );
    }

    /**
     * Tests the convertDcaValue() method.
     */
    public function testConvertsTheDcaValue()
    {
        $this->assertSame('{{article_url::5}}', $this->provider->convertDcaValue(new PickerConfig('link'), 5));
    }
}
