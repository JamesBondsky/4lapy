<?php

namespace FourPaws\SapBundle\DependencyInjection;

use FourPaws\SapBundle\Consumer\ConsumerInterface;
use FourPaws\SapBundle\Pipeline\PipelineInterface;
use FourPaws\SapBundle\Service\DirectorySourceFinderBuilder;
use FourPaws\SapBundle\Source\DirectorySource;
use FourPaws\SapBundle\Source\SourceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class FourPawsSapExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $this->registerConsumerTags($container);
        $this->registerSourceTags($container);
        $this->registerPipelineTags($container);
        $this->configDirectoryFinder($mergedConfig['directory_sources'], $container);
        $this->configPipelines($mergedConfig['pipelines'], $mergedConfig['directory_sources'], $container);
    }

    protected function registerConsumerTags(ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(ConsumerInterface::class)
            ->addTag('sap.consumer');
    }

    protected function registerSourceTags(ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(SourceInterface::class)
            ->addTag('sap.source');
    }
    
    protected function registerPipelineTags(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(PipelineInterface::class)->addTag('sap.pipeline');
    }

    /**
     * @param array            $directorySources
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function configDirectoryFinder(array $directorySources, ContainerBuilder $container)
    {
        foreach ($directorySources as $name => $source) {
            $container
                ->register('sap.source.finder.' . $name)
                ->setClass(Finder::class)
                ->addArgument($source['in'])
                ->addArgument($source['filetype'])
                ->setFactory([
                    new Reference(DirectorySourceFinderBuilder::class),
                    'build',
                ]);

            $container
                ->register('sap.source.' . $name)
                ->setClass(DirectorySource::class)
                ->addArgument(new Reference('sap.source.finder.' . $name))
                ->addArgument($source['out'])
                ->addArgument($source['error'])
                ->addTag('sap.source', ['type' => $source['entity']]);
        }
    }
    
    /**-
     * @param array            $pipelines
     * @param array            $directorySources
     * @param ContainerBuilder $container
     */
    protected function configPipelines(array $pipelines, array $directorySources, ContainerBuilder $container)
    {
        dump([$pipelines, $directorySources]);
        
        foreach ($pipelines as $name => $pipeline) {
            foreach ($pipeline as $service) {
                a
            }
        }
    }
}
