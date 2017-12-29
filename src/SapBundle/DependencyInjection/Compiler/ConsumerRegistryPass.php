<?php

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class ConsumerRegistryPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ConsumerRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(ConsumerRegistry::class);

        $taggedServices = $container->findTaggedServiceIds('sap.consumer');

        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall('register', [new Reference($id)]);
        }
    }
}
