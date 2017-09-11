<?php

namespace Contao\CoreBundle\Menu;

use Contao\CoreBundle\Event\BackendMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BackendMenuBuilder
{
    private $factory;
    private $eventDispatcher;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

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