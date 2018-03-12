<?php

namespace FourPaws\SapBundle\DependencyInjection;

use FourPaws\SapBundle\Consumer\ConsumerInterface;
use FourPaws\SapBundle\Pipeline\Pipeline;
use FourPaws\SapBundle\Service\DirectorySourceFinderBuilder;
use FourPaws\SapBundle\Service\Orders\OrderService;
use FourPaws\SapBundle\Source\CsvDirectorySource;
use FourPaws\SapBundle\Source\SerializerDirectorySource;
use FourPaws\SapBundle\Source\SourceInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class FourPawsSapExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->configDirectoryFinder($mergedConfig['directory_sources'], $container);
        $this->registerConsumerTags($container);
        $this->registerSourceTags($container);
        $this->configPipelines($mergedConfig['pipelines'], $container);
        $this->configOrderService($mergedConfig['out_path'], $container);
    }

    protected function registerConsumerTags(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ConsumerInterface::class)->addTag('sap.consumer');
    }

    protected function registerSourceTags(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(SourceInterface::class)->addTag('sap.source');
    }

    /**
     * @param array $directorySources
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function configDirectoryFinder(array $directorySources, ContainerBuilder $container)
    {
        foreach ($directorySources as $name => $source) {
            $container->register('sap.source.finder.' . $name)
                ->setClass(Finder::class)
                ->addArgument($source['filemask'])
                ->addArgument($source['in'])
                ->addArgument($source['filetype'])
                ->setFactory([
                    new Reference(DirectorySourceFinderBuilder::class),
                    'build',
                ]);

            if ($source['filetype'] === 'csv') {
                $container->register('sap.source.' . $name)
                    ->setClass(CsvDirectorySource::class)
                    ->addArgument(new Reference('sap.source.finder.' . $name))
                    ->addArgument($source['entity'])
                    ->addArgument($source['out'])
                    ->addArgument($source['error'])
                    ->addTag('sap.source', ['type' => $source['entity']]);
            } else {
                $container->register('sap.source.' . $name)
                    ->setClass(SerializerDirectorySource::class)
                    ->addArgument(new Reference('sap.source.finder.' . $name))
                    ->addArgument($source['entity'])
                    ->addArgument($source['out'])
                    ->addArgument($source['error'])
                    ->addArgument(new Reference(SerializerInterface::class))
                    ->addArgument($source['filetype'])
                    ->addTag('sap.source', ['type' => $source['entity']]);
            }
        }
    }

    /**
     *
     * @param array $pipelines
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException
     */
    protected function configPipelines(array $pipelines, ContainerBuilder $container)
    {
        $allSources = $container->findTaggedServiceIds('sap.source');

        foreach ($pipelines as $name => $pipeline) {
            $definition =
                $container->register('sap.pipeline.' . $name)
                    ->setClass(Pipeline::class)
                    ->addTag('sap.pipeline', ['name' => $name]);

            foreach ($pipeline as $pipelineSource) {
                $source = array_filter(
                    $allSources,
                    function ($value) use ($pipelineSource) {
                        return $pipelineSource === $value[0]['type'];
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (\is_array($source)) {
                    foreach ($source as $serviceId => $serviceContext) {
                        $definition->addMethodCall('add', [new Reference($serviceId)]);
                    }
                }
            }
        }
    }

    /**
     * @param array $outPath
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    protected function configOrderService(array $outPath, ContainerBuilder $container)
    {
        /**
         * @todo сделать нормальную магию
         */
        if ($outPath['order']) {
            $container->getDefinition(OrderService::class)->addMethodCall('setOutPath', [$outPath['order']]);
        }
    }
}
