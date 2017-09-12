<?php

namespace Contao\CoreBundle\Tests\Event;

use Contao\CoreBundle\Event\MenuEvent;
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

        $event = new MenuEvent($node);
        $this->assertInstanceOf('Contao\CoreBundle\Event\MenuEvent', $event);
    }

    public function testSupportsReadingNodes()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->createMock(ItemInterface::class);

        $event = new MenuEvent($node);
        $this->assertEquals($node, $event->getTree());
    }
}
