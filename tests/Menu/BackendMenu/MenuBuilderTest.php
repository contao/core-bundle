<?php

namespace Contao\CoreBundle\Tests\Menu\BackendMenu;

use Contao\CoreBundle\Event\BackendMenuEvent;
use Contao\CoreBundle\Menu\BackendMenu\MenuBuilder;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MenuBuilderTest extends TestCase
{
    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $dispatcher */
    protected $eventDispatcher;

    /** @var MenuBuilder */
    protected $builder;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->builder = new MenuBuilder(new MenuFactory(), $this->eventDispatcher);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Menu\BackendMenu\MenuBuilder', $this->builder);
    }

    /**
     * Tests if after the creation a KnpMenu node is present and its called root.
     */
    public function testCreatesARootNode()
    {
        $tree = $this->builder->create();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $tree);
        $this->assertEquals('root', $tree->getName());
    }

    /**
     * Test if within the creation process of the backend menu a BUILD_EVENT is fired.
     */
    public function testDispatchesTheMenuEvent()
    {
        $this->eventDispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with(BackendMenuEvent::BUILD_EVENT, $this->isInstanceOf('Contao\CoreBundle\Event\BackendMenuEvent'))
        ;

        $this->builder->create();
    }
}