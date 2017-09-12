<?php

namespace Contao\CoreBundle\Event;

use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class BackendMenuEvent extends Event
{
    const BUILD_EVENT = 'contao.build_backend_menu';

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
