<?php

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PipelineRegistryPass
{
    public function process(ContainerBuilder $container)
    {
        dump($container);
        if (!$container->has(PipelineRegistry::class)) {
            return;
        }
        
        $registry = $container->getDefinition(PipelineRegistry::class);
        
        $taggedServices = $container->findTaggedServiceIds('sap.pipeline');
        
        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall('register', [new Reference($id)]);
        }
    }
}
