<?php

namespace Zim\CertAuthBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zim_cert_auth');

        $rootNode
            ->children()
                ->scalarNode('ca_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->children()
                ->scalarNode('ca_key_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->children()
                ->scalarNode('ca_key_password')->end()
            ->end()
            ->children()
                ->booleanNode('disable_cert_restore')
                    ->defaultValue(false)
                ->end()
            ->end()
            ->children()
                ->arrayNode('custom_oids')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('client_csr_options')
                    ->defaultValue([])
                    ->prototype('scalar')->cannotBeEmpty()->end()
                ->end()
            ->end()
            ->children()
                ->integerNode('bit_strength')
                    ->defaultValue(4096)
                    ->validate()
                        ->ifNotInArray([1024,2048,4096])
                        ->thenInvalid('Invalid value. Must be [1024,2048,4096]')
                    ->end()
                ->end()
            ->end()
            ->children()
                ->scalarNode('cert_content_server_var')
                    ->defaultValue('CLIENT_CERT')
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->children()
                ->scalarNode('cert_validation_expression')
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->children()
                ->arrayNode('cert_storage_formatter')
                    ->children()
                        ->scalarNode('id')->end()
                        ->variableNode('options')->end()
                    ->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('cert_storage_persister')
                    ->children()
                        ->scalarNode('id')->end()
                        ->variableNode('options')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
