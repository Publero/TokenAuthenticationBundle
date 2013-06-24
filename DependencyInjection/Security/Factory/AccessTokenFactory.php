<?php
namespace Publero\TokenAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class AccessTokenFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.access_token.' . $id;
        $decorator = new DefinitionDecorator('publero_token_authentication.security.authentication.provider.access_token');
        $container->setDefinition($providerId, $decorator)->replaceArgument(0, new Reference($userProvider));

        $container->setAlias('publero_token_authentication.user_provider', $userProvider);

        $listenerId = 'security.authentication.listener.access_token.' . $id;
        $decorator = new DefinitionDecorator('publero_token_authentication.security.authentication.listener.access_token');
        $container->setDefinition($listenerId, $decorator);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'access_token';
    }

    public function addConfiguration(NodeDefinition $builder)
    {
    }
}
