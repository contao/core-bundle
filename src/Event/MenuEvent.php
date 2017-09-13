<?php

namespace Contao\CoreBundle\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class MenuEvent extends Event
{
    private $factory;
    private $tree;

    public function __construct(FactoryInterface $factory, ItemInterface $tree)
    {
        $this->factory = $factory;
        $this->tree = $tree;
    }

    public function getTree()
    {
        return $this->tree;
    }

    public function getFactory()
    {
        return $this->factory;
    }
}
