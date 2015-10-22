<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a page is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PageEvent extends Event
{
    /**
     * @var PageModel
     */
    private $page;

    /**
     * @var LayoutModel
     */
    private $layout;

    /**
     * @var PageRegular
     */
    private $handler;

    /**
     * Constructor.
     *
     * @param PageModel   $page    The page model
     * @param LayoutModel $layout  The layout model
     * @param PageRegular $handler The page handler
     */
    public function __construct(PageModel &$page, LayoutModel &$layout, PageRegular &$handler)
    {
        $this->page    = &$page;
        $this->layout  = &$layout;
        $this->handler = &$handler;
    }

    /**
     * Returns the page model.
     *
     * @return PageModel The page model
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the page model.
     *
     * @param PageModel $page The page
     */
    public function setPage(PageModel $page)
    {
        $this->page = $page;
    }

    /**
     * Returns the layout model.
     *
     * @return LayoutModel The layout model
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Sets the layout model.
     *
     * @param LayoutModel $layout The layout model
     */
    public function setLayout(LayoutModel $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Returns the page handler.
     *
     * @return PageRegular The page handler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Sets the page handler.
     *
     * @param PageRegular $handler The page handler
     */
    public function setHandler(PageRegular $handler)
    {
        $this->handler = $handler;
    }
}
