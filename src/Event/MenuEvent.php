<?php

namespace Contao\CoreBundle\Event;

use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class MenuEvent extends Event
{
    private $tree;

    public function __construct(ItemInterface $tree)
    {
        $this->tree = $tree;
    }

    public function getTree()
    {
        return $this->tree;
    }
}
