<?php

namespace Contao\CoreBundle\DependencyInjection\Security\Factory;

use Contao\CoreBundle\Security\Firewall\FrontendAuthenticationListener;
use Contao\CoreBundle\Security\Authentication\Provider\FrontendAuthenticationProvider;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FrontendAuthenticationFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.login_module.'.$id;

        $container
            ->setDefinition($providerId, new Definition(
                    FrontendAuthenticationProvider::class,
                    [
                        new Reference($userProvider),
                        new Reference('security.user_checker'),
                        'contao_frontend',
                        new Reference('security.encoder_factory.generic'),
                        false,
                    ]
            ))
        ;

        $listenerId = 'security.authentication.listener.login_module.'.$id;

        $container
            ->setDefinition($listenerId, new Definition(
                FrontendAuthenticationListener::class,
                [
                    new Reference('security.token_storage'),
                    new Reference('security.authentication.manager'),
                    'contao_frontend',
                ]
            ))
        ;

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'login_module';
    }

    public function addConfiguration(NodeDefinition $builder)
    {

    }

}
