<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener\Hook;

use Contao\CoreBundle\Event\PageEvent;
use Contao\CoreBundle\EventListener\Hook\GetPageLayoutListener;
use Contao\CoreBundle\Test\TestCase;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

/**
 * Tests the GetPageLayoutListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class GetPageLayoutListenerTest extends TestCase
{
    /**
     * @var GetPageLayoutListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = new GetPageLayoutListener();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\GetPageLayoutListener', $this->listener);
    }

    /**
     * Tests the onGetPageLayout() method.
     */
    public function testOnGetPageLayout()
    {
        $page    = new PageModel();
        $layout  = new LayoutModel();
        $handler = new PageRegular();

        $page->setRow(['foo' => 'bar']);
        $layout->setRow(['foo' => 'bar']);

        $event = new PageEvent($page, $layout, $handler);

        $GLOBALS['TL_HOOKS']['getPageLayout'][] = function (
            PageModel $page,
            LayoutModel $layout
        ) {
            $page->setRow(['foo' => 'baz']);
            $layout->setRow(['foo' => 'baz']);
        };

        $this->listener->onGetPageLayout($event);

        $this->assertEquals(['foo' => 'baz'], $page->row());
        $this->assertEquals(['foo' => 'baz'], $layout->row());

        unset($GLOBALS['TL_HOOKS']);
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $page    = new PageModel();
        $layout  = new LayoutModel();
        $handler = new PageRegular();

        $page->setRow(['foo' => 'bar']);
        $layout->setRow(['foo' => 'bar']);

        $event = new PageEvent($page, $layout, $handler);

        $page2    = new PageModel();
        $layout2  = new LayoutModel();
        $handler2 = new PageRegular();

        $GLOBALS['TL_HOOKS']['getPageLayout'][] = function (
            PageModel &$page,
            LayoutModel &$layout,
            PageRegular &$handler
        ) use ($page2, $layout2, $handler2) {
            $page    = $page2;
            $layout  = $layout2;
            $handler = $handler2;
        };

        $this->listener->onGetPageLayout($event);

        $this->assertEquals($page2, $page);
        $this->assertEquals($layout2, $layout);
        $this->assertEquals($handler2, $handler);

        unset($GLOBALS['TL_HOOKS']);
    }
}
