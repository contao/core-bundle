<?php

namespace Contao\CoreBundle\Tests\Menu;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Menu\BackendMenuBuilder;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BackendMenuBuilderTest extends TestCase
{
    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $dispatcher */
    protected $eventDispatcher;

    /** @var BackendMenuBuilder */
    protected $builder;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->builder = new BackendMenuBuilder(new MenuFactory(), $this->eventDispatcher);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Menu\BackendMenuBuilder', $this->builder);
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
            ->with(ContaoCoreEvents::BACKEND_MENU_BUILD, $this->isInstanceOf('Contao\CoreBundle\Event\MenuEvent'))
        ;

        $this->builder->create();
    }
}