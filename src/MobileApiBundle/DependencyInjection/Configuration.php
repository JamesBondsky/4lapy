<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('four_paws_mobile_api');
        /** @noinspection NullPointerExceptionInspection */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('debug')->defaultValue(false)->end()
            ->append($this->getExceptionsMap())
            ->end();
        return $treeBuilder;
    }

    public function getExceptionsMap()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('exceptions');

        /** @noinspection NullPointerExceptionInspection */
        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('code')
                    ->end()
                    ->scalarNode('message')
                    ->end()
                    ->scalarNode('status_code')
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
