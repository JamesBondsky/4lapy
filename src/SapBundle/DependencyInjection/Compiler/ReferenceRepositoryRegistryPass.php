<?php

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\ReferenceDirectory\ReferenceRepositoryRegistry;
use FourPaws\SapBundle\Repository\ReferenceRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ReferenceRepositoryRegistryPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ReferenceRepositoryRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(ReferenceRepositoryRegistry::class);

        $taggedServices = $container->findTaggedServiceIds('sap.reference');

        foreach ($taggedServices as $id => $tags) {
            $property = $tags[0]['property'] ?: '';
            if ($property) {
                $repositoryId = 'sap.reference.repository.' . strtolower($property);
                $definition = new Definition(ReferenceRepository::class, [new Reference($id)]);
                $container->setDefinition($repositoryId, $definition);

                $registry->addMethodCall('register', [$property, new Reference($repositoryId)]);
            }
        }
    }
}
