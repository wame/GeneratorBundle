<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('wame_sensio_generator');
        $rootNode
            ->children()
                ->scalarNode('default_bundle')
                    ->defaultValue('AppBundle')
                    ->validate()
                    ->ifTrue(function ($name) {
                        return !preg_match('/.Bundle$/', $name);
                    })
                        ->thenInvalid('A bundle name in "default_bundle should" end with "Bundle"')
                    ->end()
                ->end()
            ->end()
            ->append($this->getClassNode())
            ->append($this->getCrudNode())
        ;

        return $treeBuilder;
    }

    protected function getClassNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('class');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('softdeleteable_trait')
                    ->cannotBeEmpty()
                    ->defaultValue('Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity')
                ->end()
                ->scalarNode('iptraceable_trait')
                    ->cannotBeEmpty()
                    ->defaultValue('Gedmo\\IpTraceable\\Traits\\IpTraceableEntity')
                ->end()
                ->scalarNode('timestampable_trait')
                    ->cannotBeEmpty()
                    ->defaultValue('Gedmo\\Timestampable\\Traits\\TimestampableEntity')
                ->end()
                ->scalarNode('blameable_trait')
                    ->cannotBeEmpty()
                    ->defaultValue('Gedmo\\Blameable\\Traits\\BlameableEntity')
                ->end()
                ->scalarNode('nestedset_trait')
                    ->cannotBeEmpty()
                    ->defaultValue('Gedmo\\Tree\\Traits\\NestedSetEntity')
                ->end()
                ->scalarNode('blameable_repository_trait')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        return $node;
    }

    protected function getCrudNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('crud');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('datatables')
                    ->defaultTrue()
                    ->info('Use Datatables on CRUD index by default when not set to false')
                ->end()
            ->end()
        ;
        return $node;
    }
}
