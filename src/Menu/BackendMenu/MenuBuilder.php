<?php

namespace Contao\CoreBundle\Menu\BackendMenu;

use Contao\CoreBundle\Event\BackendMenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MenuBuilder
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
        $this->eventDispatcher->dispatch(BackendMenuEvent::BUILD_EVENT, new BackendMenuEvent($tree));

        // Annotate nodes, ie. active states
        $this->eventDispatcher->dispatch(BackendMenuEvent::ANNOTATE_EVENT, new BackendMenuEvent($tree));

        return $tree;
    }
}