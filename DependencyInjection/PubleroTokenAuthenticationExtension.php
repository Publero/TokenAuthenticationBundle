<?php
namespace Publero\TokenAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PubleroTokenAuthenticationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('publero_token_authentication.model_manager_name', $config['model_manager_name']);
        $container->setParameter('publero_token_authentication.backend_type_' . $config['db_driver'], true);
        $container->setParameter('publero_token_authentication.access_token_class', $config['access_token_class']);
        $container->setParameter('publero_token_authentication.access_token_lenght', $config['access_token_length']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('%s.yml', $config['db_driver']));
        $loader->load('services.yml');
    }
}
