<?php

namespace Contao\CoreBundle\Tests\Event;

use Contao\CoreBundle\Event\BackendMenuEvent;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;

class BackendMenuEventTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->createMock(ItemInterface::class);

        $event = new BackendMenuEvent($node);
        $this->assertInstanceOf('Contao\CoreBundle\Event\BackendMenuEvent', $event);
    }

    public function testSupportsReadingAndWritingNodes()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->createMock(ItemInterface::class);

        $event = new BackendMenuEvent($node);
        $this->assertEquals($node, $event->getTree());

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $changedNode */
        $changedNode = $this->createMock(ItemInterface::class);
        $event->setTree($changedNode);

        $this->assertEquals($changedNode, $event->getTree());
    }
}
