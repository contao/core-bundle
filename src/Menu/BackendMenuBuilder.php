<?php

namespace Contao\CoreBundle\Menu;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BackendMenuBuilder
{
    private $factory;
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param FactoryInterface $factory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(FactoryInterface $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Creates a new root menu node and dispatches menu creation
     * events to fill it with child nodes.
     *
     * @return ItemInterface
     */
    public function create()
    {
        $tree = $this->factory->createItem('root');

        // Nodes can be attached via an event listener
        $this->eventDispatcher->dispatch(ContaoCoreEvents::BACKEND_MENU_BUILD, new MenuEvent($tree));

        return $tree;
    }
}