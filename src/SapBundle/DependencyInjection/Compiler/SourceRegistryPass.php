<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\Source\SourceRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SourceRegistryPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(SourceRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(SourceRegistry::class);

        $taggedServices = $container->findTaggedServiceIds('sap.source');

        foreach ($taggedServices as $id => $tags) {
            $type = $tags[0]['type'] ?: '';
            if ($type) {
                $registry->addMethodCall('register', [$type, new Reference($id)]);
            }
        }
    }
}
