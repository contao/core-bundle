<?php
/**
 * Created by PhpStorm.
 * User: yanickwitschi
 * Date: 04/09/15
 * Time: 16:09
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddInsertTagParsersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // InsertTags
        if ($container->hasDefinition('contao.insert_tag_collection')) {
            $definition = $container->getDefinition('contao.insert_tag_collection');

            $insertTags = $this->findByTag('contao.insert_tag', $container);

            foreach ($insertTags as $insertTag) {
                $definition->addMethodCall('add', array(new Reference($insertTag)));
            }
        }

        // InsertTagFlags
        if ($container->hasDefinition('contao.insert_tag_flag_collection')) {
            $definition = $container->getDefinition('contao.insert_tag_flag_collection');

            $insertTagFlags = $this->findByTag('contao.insert_tag_flag', $container);

            foreach ($insertTagFlags as $insertTagFlag) {
                $definition->addMethodCall('add', array(new Reference($insertTagFlag)));
            }
        }
    }

    private function findByTag($tag, ContainerBuilder $container)
    {
        $stack = new \SplPriorityQueue();
        $order = PHP_INT_MAX;

        foreach ($container->findTaggedServiceIds($tag) as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;

            $stack->insert($id, array($priority, --$order));
        }

        return $stack;
    }
}
