<?php

namespace Contao\CoreBundle\EventListener\BackendMenu;

use Contao\CoreBundle\Event\BackendMenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StateListener
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function collapseItems(BackendMenuEvent $event)
    {
        $tree = $event->getTree();
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $sessionData = $session->getBag('contao_backend')->all();

        foreach ($sessionData['backend_modules'] as $module => $expanded) {
            if ($expanded) {
                continue;
            }

            $tree->getChild($module)->setDisplayChildren(false);
        }
    }

    public function setActiveState(BackendMenuEvent $event)
    {
        $tree = $event->getTree();
        $request = $this->requestStack->getCurrentRequest();

        $this->setActiveStateOnNode($tree, $request->get('do'));

    }

    protected function setActiveStateOnNode(ItemInterface $item, $active)
    {
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $hasActiveChild = $this->setActiveStateOnNode($child, $active);

                if ($hasActiveChild) {
                    $item->setAttribute('trail', true);
                }
            }
        }

        if ($item->getName() === $active) {
            $item->setCurrent(true);

            return true;
        }

        return false;
    }
}