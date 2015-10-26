<?php

namespace DemoBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('demo');

        $rootNode
            ->children()
            ->arrayNode('users')
            ->prototype('scalar')
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}