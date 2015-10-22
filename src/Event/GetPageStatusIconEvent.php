<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\PageModel;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a page status icon is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetPageStatusIconEvent extends Event
{
    /**
     * @var string
     */
    private $image;

    /**
     * @var PageModel
     */
    private $page;

    /**
     * Constructor.
     *
     * @param string    $image The image path
     * @param PageModel $page  The page model
     */
    public function __construct($image, PageModel &$page)
    {
        $this->image = $image;
        $this->page  = &$page;
    }

    /**
     * Returns the image path.
     *
     * @return string The image path
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets the image path.
     *
     * @param string $image The image path
     */
    public function setImage($image)
    {
        $this->image = $image;
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
     * @param PageModel $page The page model
     */
    public function setPage(PageModel $page)
    {
        $this->page = $page;
    }
}
