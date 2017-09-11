<?php

namespace Contao\CoreBundle\EventListener\BackendMenu;

use Contao\CoreBundle\Event\BackendMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LegacyMenuListener
{
    private $factory;
    private $requestStack;

    public function __construct(FactoryInterface $factory, RequestStack $requestStack)
    {
        $this->factory = $factory;
        $this->requestStack = $requestStack;
    }

    public function onBuild(BackendMenuEvent $event)
    {
        $tree = $event->getTree();
        $modules = $GLOBALS['BE_MOD'];
        $refererId = $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id');

        foreach ($modules as $category => $nodes) {
            // Create a category node if it doesn't exist yet
            if (!$tree->getChild($category)) {
                $node = $this->createCategory($category, [
                    'label' => $this->getTranslatedLabel($category),
                    'route' => 'contao_backend',
                    'routeParameters' => [
                        'do' => $this->requestStack->getCurrentRequest()->get('do'),
                        'mtg' => $category,
                        'ref' => $refererId
                    ],
                    'attributes' => [
                        'title' => \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['collapseNode']),
                        'icon' => 'modPlus.gif',
                        'trail' => false
                    ]
                ]);

                $node->setDisplayChildren(true);

                $tree->addChild($node);
            }

            // Create the child nodes
            foreach ($nodes as $nodeName => $nodeOptions) {

                $node = $this->createNode($nodeName, [
                    'label' => $this->getTranslatedLabel($nodeName),
                    'route' => 'contao_backend',
                    'routeParameters' => ['do' => $nodeName, 'ref' => $refererId],
                    'attributes' => [
                        'title' => \StringUtil::specialchars($GLOBALS['TL_LANG']['MOD'][$nodeName][1]),
                        'class' => $nodeName,
                        'trail' => false
                    ]
                ]);

                $node->setCurrent(false);

                $tree->getChild($category)->addChild($node);
            }
        }

        return $tree;
    }

    private function createCategory($name, array $options = [])
    {
        return $this->factory->createItem($name, $options);
    }

    private function createNode($name, array $options)
    {
        return $this->factory->createItem($name, $options);
    }

    private function getTranslatedLabel($nodeName)
    {
        return (($label = is_array($GLOBALS['TL_LANG']['MOD'][$nodeName]) ? $GLOBALS['TL_LANG']['MOD'][$nodeName][0] : $GLOBALS['TL_LANG']['MOD'][$nodeName]) != false) ? $label : $nodeName;
    }
}