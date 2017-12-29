<?php

namespace FourPaws\SapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @throws \RuntimeException
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('four_paws_sap');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->append($this->getDirectorySourceNode())
            ->end();
        return $treeBuilder;
    }

    /**
     * @throws \RuntimeException
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public function getDirectorySourceNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('directory_sources');

        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('entity')->isRequired()->end()
            ->scalarNode('in')->isRequired()->end()
            ->scalarNode('out')->isRequired()->end()
            ->scalarNode('error')->isRequired()->end()
            ->scalarNode('filetype')->defaultValue('xml')->end()
            ->end()
            ->end();
        return $node;
    }
}
