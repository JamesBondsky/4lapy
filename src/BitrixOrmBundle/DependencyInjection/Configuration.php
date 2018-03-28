<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\DependencyInjection;

use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @throws \RuntimeException
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('four_paws_bitrix_orm');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->append($this->getEntitiesNode())
            ->end();
        return $treeBuilder;
    }

    protected function getEntitiesNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('entities');

        /** @noinspection NullPointerExceptionInspection */
        $node
            ->useAttributeAsKey('name')
                ->arrayPrototype()
                ->children()
                    ->scalarNode('class')->cannotBeEmpty()->isRequired()->end()
                    ->scalarNode('repository')
                    ->defaultValue(D7Repository::class)
                    ->end()
                    ->booleanNode('d7')
                    ->defaultTrue()
                    ->end()
                    ->scalarNode('data_manager')->defaultNull()->end()
                    ->arrayNode('select')
                        ->defaultValue(['*'])
                        ->variablePrototype()->end()
                    ->end()
                    ->arrayNode('filter')
                        ->variablePrototype()->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
