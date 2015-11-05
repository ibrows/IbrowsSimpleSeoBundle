<?php

namespace Ibrows\SimpleSeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
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
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('ibrows_simple_seo');
        $this->addTemplateSection($node);

        return $treeBuilder;
    }

    private function addTemplateSection(\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('entity_class')->defaultValue('Ibrows\SimpleSeoBundle\Entity\MetaTagContent')->end()
                ->booleanNode('localized_alias')->defaultTrue()->end()
                ->booleanNode('add_query_string')->defaultFalse()->end()
                ->arrayNode('admin')->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('allow_create')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('alias')->addDefaultsIfNotSet()->children()
                    ->scalarNode('maxLength')->defaultValue(100)->end()
                    ->scalarNode('separatorUnique')->defaultValue('-')->end()
                    ->scalarNode('separator')->defaultValue('/')->end()
                    ->scalarNode('notAllowedCharsPattern')->defaultValue('![^-a-z0-9_]+!')->end()
                    ->arrayNode('sortOrder')->defaultValue(array())->prototype('scalar')->end()
                ->end()->end()
            ->end();
    }
}
