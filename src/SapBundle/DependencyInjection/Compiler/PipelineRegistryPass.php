<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PipelineRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(PipelineRegistry::class)) {
            return;
        }
        
        $registry = $container->getDefinition(PipelineRegistry::class);
        
        $taggedServices = $container->findTaggedServiceIds('sap.pipeline');
        
        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall(
                'register',
                                     [
                                         $tags[0]['name'],
                                         new Reference($id),
                                     ]
            );
        }
    }
}
