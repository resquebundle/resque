<?php

namespace ResqueBundle\Resque\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('resque');

        if (method_exists($treeBuilder,'getRootNode')){
            // Symfony 4+
            $root =   $treeBuilder->getRootNode();
        } else {
            // Symfony 3
            $root = $treeBuilder->root('resque');
        }
        
        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('vendor_dir')
                    ->defaultValue('%kernel.root_dir%/../vendor')
                    ->cannotBeEmpty()
                    ->info('Set the vendor dir')
                ->end()
                ->scalarNode('app_include')
                    ->defaultValue(null)
                    ->info('Set the APP_INCLUDE for php-resque')
                ->end()
                ->scalarNode('prefix')
                    ->defaultNull()
                    ->end()
                ->scalarNode('class')
                    ->defaultValue('ResqueBundle\Resque\Resque')
                    ->cannotBeEmpty()
                    ->info('Set the resque class dir')
                ->end()
                ->arrayNode('auto_retry')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function($var) {
                            if (array_key_exists(0, $var)) {
                                return [$var];
                            }
                            return $var;
                        })
                    ->end()
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                    ->info('Set auto retry strategy')
                ->end()
                ->arrayNode('redis')
                    ->info('Redis configuration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')
                            ->defaultValue('localhost')
                            ->cannotBeEmpty()
                            ->info('The redis hostname')
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue(6379)
                            ->cannotBeEmpty()
                            ->info('The redis port')
                        ->end()
                        ->scalarNode('database')
                            ->defaultValue(0)
                            ->info('The redis database')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('worker')
                    ->info('Worker Server configuration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('root_dir')
                            ->defaultValue('%kernel.root_dir%')
                            ->cannotBeEmpty()
                            ->info('The root dir of worker registered app')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
