<?php

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserBackendMenuListener
{
    private $factory;
    private $tokenStorage;

    public function __construct(FactoryInterface $factory, TokenStorageInterface $tokenStorage)
    {
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
    }

    public function onBuild(MenuEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token || !($user = $token->getUser()) instanceof BackendUser) {
            return;
        }

        $tree = $event->getTree();
        $modules = $user->navigation();

        foreach ($modules as $categoryName => $categoryData) {
            $categoryNode = $tree->getChild($categoryName);

            if (!$categoryNode) {
                $categoryNode = $this->createNode($categoryName, $categoryData);
                $categoryNode->setDisplayChildren(strpos($categoryData['class'], 'node-expanded') !== false);

                $tree->addChild($categoryNode);
            }

            // Create the child nodes
            foreach ($categoryData['modules'] as $moduleName => $moduleData) {
                $moduleNode = $this->createNode($moduleName, $moduleData);
                $moduleNode->setCurrent((bool) $moduleData['isActive']);
                $moduleNode->setAttribute('class', $categoryName);

                $categoryNode->addChild($moduleNode);
            }
        }
    }

    private function createNode($name, array $attributes)
    {
        return $this->factory->createItem($name, [
            'label' => $attributes['label'],
            'attributes' => [
                'title' => $attributes['title'],
                'href' => $attributes['href']
            ]
        ]);
    }
}