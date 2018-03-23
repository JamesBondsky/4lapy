<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\DependencyInjection;

use Bitrix\Main\Entity\DataManager;
use FourPaws\BitrixOrmBundle\Exception\InvalidArgumentException;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\BitrixOrmBundle\Orm\D7EntityManager;
use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\BitrixOrmBundle\Orm\D7RepositoryInterface;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FourPawsBitrixOrmExtension extends ConfigurableExtension
{

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->configRepositories($mergedConfig['entities'], $container);
    }

    /**
     * @param array            $entities
     * @param ContainerBuilder $containerBuilder
     * @throws \Exception
     */
    protected function configRepositories(array $entities, ContainerBuilder $containerBuilder): void
    {
        $ormDefinition = $containerBuilder->getDefinition(BitrixOrm::class);
        foreach ($entities as $entityName => $entityData) {
            if ($entityData['d7'] !== true) {
                throw new InvalidArgumentException('Not d7 entity now not allowed');
            }

            if (!is_a($entityData['repository'], D7RepositoryInterface::class, true)) {
                throw new InvalidArgumentException('Not D7RepositoryInterface repository now not allowed');
            }

            if (!$entityData['data_manager']) {
                throw new InvalidArgumentException('No DataManager was passed');
            }

            $entityManagerId = 'four_paws_bitrix_orm.entities.' . $entityName . '.manager';
            $repositoryId = 'four_paws_bitrix_orm.entities.' . $entityName . '.repository';

            $this->createDataManagerDefinition($entityData['data_manager'], $containerBuilder);

            $entityManagerDefinition = new Definition(D7EntityManager::class);
            $entityManagerDefinition->setPublic(false);
            $entityManagerDefinition->setArgument(0, $entityData['class']);
            $entityManagerDefinition->setArgument(1, new Reference(ValidatorInterface::class));
            $entityManagerDefinition->setArgument(2, new Reference(ArrayTransformerInterface::class));
            $entityManagerDefinition->setArgument(3, new Reference($entityData['data_manager']));
            $containerBuilder->setDefinition($entityManagerId, $entityManagerDefinition);


            $repositoryDefinition = new Definition(D7Repository::class);
            $repositoryDefinition->setArgument(0, new Reference($entityManagerId));
            $repositoryDefinition->addMethodCall('setSelect', [$entityData['select']]);
            $repositoryDefinition->addMethodCall('setFilter', [$entityData['filter']]);

            $containerBuilder->setDefinition($repositoryId, $repositoryDefinition);

            $ormDefinition->addMethodCall(
                'addD7Repository',
                [new Reference($repositoryId)]
            );
        }
    }

    /**
     * @param string           $dataManager
     * @param ContainerBuilder $containerBuilder
     * @throws \FourPaws\BitrixOrmBundle\Exception\InvalidArgumentException
     */
    protected function createDataManagerDefinition(string $dataManager, ContainerBuilder $containerBuilder): void
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        if (is_a($dataManager, DataManager::class, true) && !$containerBuilder->hasDefinition($dataManager)) {
            $definition = new Definition($dataManager);
            $definition->setPublic(false);
            $definition->setSynthetic(true);
            $containerBuilder->setDefinition($dataManager, $definition);
        }
    }
}
