<?php

namespace Contao\CoreBundle\Tests\EventListener\BackendMenu;

use Contao\BackendUser;
use Contao\CoreBundle\Event\BackendMenuEvent;
use Contao\CoreBundle\EventListener\BackendMenu\UserMenuListener;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserMenuListenerTest extends TestCase
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    protected function setUp()
    {
        $user = $this
            ->getMockBuilder(BackendUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasAccess', 'navigation'])
            ->getMock()
        ;

        $user
            ->method('hasAccess')
            ->willReturn(true)
        ;

        $user
            ->method('navigation')
            ->willReturn([
                'category1' => [
                    'label' => 'Category 1',
                    'title' => 'Category 1 Title',
                    'href' => '/',
                    'class' => 'node-expanded',
                    'modules' => [
                        'node1' => [
                            'label' => 'Node 1',
                            'title' => 'Node 1 Title',
                            'href' => '/node1',
                            'isActive' => true
                        ],
                        'node2' => [
                            'label' => 'Node 2',
                            'title' => 'Node 2 Title',
                            'href' => '/node2',
                            'isActive' => false
                        ]
                    ]
                ],
                'category2' => [
                    'label' => 'Category 2',
                    'title' => 'Category 2 Title',
                    'href' => '/',
                    'class' => 'node-collapsed',
                    'modules' => []
                ]
            ])
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

        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $listener = new UserMenuListener(new MenuFactory(), $this->tokenStorage);
        $this->assertInstanceOf('Contao\CoreBundle\EventListener\BackendMenu\UserMenuListener', $listener);
    }

    public function testConvertsLegacyArrayToNodeList()
    {
        $nodeFactory = new MenuFactory();
        $rootNode = $nodeFactory->createItem('root');

        $event = new BackendMenuEvent($rootNode);

        $listener = new UserMenuListener(new MenuFactory(), $this->tokenStorage);
        $listener->onBuild($event);

        $tree = $event->getTree();

        // Test root node
        $this->assertInstanceOf(ItemInterface::class, $tree);
        $this->assertEquals(2, count($tree->getChildren()));

        // Test category node
        $categoryNode = $tree->getChild('category1');
        $this->assertInstanceOf(ItemInterface::class, $categoryNode);
        $this->assertEquals(2, count($categoryNode->getChildren()));

        // Test module node
        $moduleNode = $categoryNode->getChild('node1');
        $this->assertInstanceOf(ItemInterface::class, $moduleNode);
        $this->assertEquals(0, count($moduleNode->getChildren()));

        // Test expanded/collapsed
        $this->assertTrue($tree->getChild('category1')->getDisplayChildren());
        $this->assertFalse($tree->getChild('category2')->getDisplayChildren());

        // Test active/not active
        $this->assertTrue($categoryNode->getChild('node1')->isCurrent());
        $this->assertFalse($categoryNode->getChild('node2')->isCurrent());
    }
}
