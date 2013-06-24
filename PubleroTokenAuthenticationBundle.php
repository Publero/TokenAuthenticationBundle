<?php
namespace Publero\TokenAuthenticationBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Publero\TokenAuthenticationBundle\DependencyInjection\Security\Factory\AccessTokenFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PubleroTokenAuthenticationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new AccessTokenFactory());

        $mappings = array(
            realpath(__DIR__ . '/Resources/config/doctrine/model') => 'Publero\TokenAuthenticationBundle\Model',
        );

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                array('publero_token_authentication.model_manager_name'),
                'publero_token_authentication.backend_type_orm'
            ));
        }

        if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver(
                $mappings,
                array('publero_token_authentication.model_manager_name'),
                'publero_token_authentication.backend_type_mongodb'
            ));
        }
    }
}
