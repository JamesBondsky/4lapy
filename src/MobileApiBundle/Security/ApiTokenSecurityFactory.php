<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiTokenSecurityFactory implements SecurityFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint): array
    {
        $providerId = 'security.authentication.provider.api_token.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition(ApiTokenProvider::class))
            ->setArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.api_token.' . $id;
        $container->setDefinition($listenerId, new ChildDefinition(ApiTokenListener::class));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): string
    {
        return 'pre_auth';
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return 'api_token';
    }

    public function addConfiguration(NodeDefinition $builder)
    {
    }
}
