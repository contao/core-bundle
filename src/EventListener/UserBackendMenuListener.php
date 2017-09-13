<?php

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserBackendMenuListener
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param MenuEvent $event
     */
    public function onBuild(MenuEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token || !($user = $token->getUser()) instanceof BackendUser) {
            return;
        }

        $factory = $event->getFactory();
        $tree = $event->getTree();
        $modules = $user->navigation();

        foreach ($modules as $categoryName => $categoryData) {
            $categoryNode = $tree->getChild($categoryName);

            if (!$categoryNode) {
                $categoryNode = $this->createNode($factory, $categoryName, $categoryData);
                $categoryNode->setDisplayChildren(strpos($categoryData['class'], 'node-expanded') !== false);

                $tree->addChild($categoryNode);
            }

            // Create the child nodes
            foreach ($categoryData['modules'] as $moduleName => $moduleData) {
                $moduleNode = $this->createNode($factory, $moduleName, $moduleData);
                $moduleNode->setCurrent((bool) $moduleData['isActive']);
                $moduleNode->setAttribute('class', $categoryName);

                $categoryNode->addChild($moduleNode);
            }
        }
    }

    /**
     * @param FactoryInterface $factory
     * @param $name
     * @param array $attributes
     * @return ItemInterface
     */
    private function createNode(FactoryInterface $factory, $name, array $attributes)
    {
        return $factory->createItem($name, [
            'label' => $attributes['label'],
            'attributes' => [
                'title' => $attributes['title'],
                'href' => $attributes['href']
            ]
        ]);
    }
}