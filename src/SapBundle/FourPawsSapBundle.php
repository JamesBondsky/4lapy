<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle;

use FourPaws\SapBundle\DependencyInjection\Compiler\ConsumerRegistryPass;
use FourPaws\SapBundle\DependencyInjection\Compiler\PipelineRegistryPass;
use FourPaws\SapBundle\DependencyInjection\Compiler\ReferenceRepositoryRegistryPass;
use FourPaws\SapBundle\DependencyInjection\Compiler\SourceRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FourPawsSapBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConsumerRegistryPass());
        $container->addCompilerPass(new SourceRegistryPass());
        $container->addCompilerPass(new ReferenceRepositoryRegistryPass());
        $container->addCompilerPass(new PipelineRegistryPass());
    }
}
