<?php
namespace Publero\TokenAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('publero_token_authentication');

        $supportedDrivers = array('orm', 'mongodb');

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('access_token_class')
                    ->isRequired()
                    ->validate()
                        ->ifTrue(function($v) {
                            return !class_exists($v);
                        })
                        ->thenInvalid('Access token class %s does\'t exist.')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) {
                            return !is_subclass_of($v, 'Publero\TokenAuthenticationBundle\Model\AccessToken');
                        })
                        ->thenInvalid('Access token class %s is not a subclass of "Publero\TokenAuthenticationBundle\Model\AccessToken".')
                    ->end()
                ->end()
                ->scalarNode('access_token_length')->defaultValue(64)->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
