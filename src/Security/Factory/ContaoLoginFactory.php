<?php

namespace Contao\CoreBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContaoLoginFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();

        $this->addOption('username_parameter', 'username');
        $this->addOption('password_parameter', 'password');
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'contao-login';
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'contao.security.authentication_provider.'.$id;
        $container
            ->setDefinition($provider, new ChildDefinition('contao.security.authentication_provider'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(1, new Reference('security.user_checker.'.$id))
            ->replaceArgument(2, $id)
        ;

        return $provider;
    }
}
