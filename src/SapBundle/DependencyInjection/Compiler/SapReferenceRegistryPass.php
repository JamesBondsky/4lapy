<?php

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\ReferenceDirectory\SapReferenceRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class SapReferenceRegistryPass implements CompilerPassInterface
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
        if (!$container->has(SapReferenceRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(SapReferenceRegistry::class);

        $taggedServices = $container->findTaggedServiceIds('sap.reference');

        foreach ($taggedServices as $id => $tags) {
            $property = $tags[0]['type'] ?: '';
            if ($property) {
                $registry->addMethodCall('register', [$property, new Reference($id)]);
            }
        }
    }
}
