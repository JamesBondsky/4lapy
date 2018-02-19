<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\DependencyInjection;

use FourPaws\MobileApiBundle\Serialization\ExceptionHandler;
use FourPaws\MobileApiBundle\Util\ExceptionDataMap;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class FourPawsMobileApiExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $this->configExceptionsMap($mergedConfig, $container);

        $debug = $mergedConfig['debug'] ?? false;
        $debug = (bool)$debug;
        $container
            ->getDefinition(ExceptionHandler::class)
            ->addMethodCall('setDebug', [$debug]);
    }

    /**
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     */
    protected function configExceptionsMap(array $mergedConfig, ContainerBuilder $container)
    {
        $exceptionsMap = $mergedConfig['exceptions'] ?? [];
        $definition = $container->getDefinition(ExceptionDataMap::class);
        $definition->setArgument(0, $exceptionsMap);
    }
}
