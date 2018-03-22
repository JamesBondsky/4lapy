<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\DependencyInjection\Compiler;

use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PipelineRegistryPass
 *
 * @package FourPaws\SapBundle\DependencyInjection\Compiler
 */
class PipelineRegistryPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container): void
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
