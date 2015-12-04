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
            ->scalarNode('client_auth')
            ->end()
            ->scalarNode('client_secret')
            ->end()
            ->scalarNode('public_id')
            ->end()
            ->scalarNode('base_url')
            ->end()
            ->scalarNode('oauth_path')
            ->end()
            ->scalarNode('api_path')
            ->end()
            ->scalarNode('api_key')
            ->end()
            ->scalarNode('api_key_type')
            ->end()
            ->scalarNode('api_key_name')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
