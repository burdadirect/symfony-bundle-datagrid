<?php

namespace HBM\DatagridBundle\DependencyInjection;

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
    $rootNode = $treeBuilder->root('hbm_datagrid');

    $rootNode
      ->children()
        ->scalarNode('translation_domain')->defaultValue(false)->end()
        ->arrayNode('session')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('prefix')->defaultValue('hbm_datagrid:')->end()
            ->arrayNode('use_for')
              ->prototype('scalar')->end()
              ->defaultValue(array('num', 'sort'))
            ->end()
          ->end()
        ->end()
        ->arrayNode('query')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('encode')->defaultValue('json')->end()
            ->arrayNode('param_names')
              ->children()
                ->scalarNode('current_page')->defaultValue('sort')->end()
                ->scalarNode('max_entries')->defaultValue('sort')->end()
                ->scalarNode('sortation')->defaultValue('sort')->end()
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('datagrid')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('sort')->defaultTrue()->end()
            ->scalarNode('extended')->defaultTrue()->end()
            ->scalarNode('multi_sort')->defaultFalse()->end()
            ->scalarNode('max_entries_per_page')->defaultValue(20)->end()
          ->end()
        ->end()
        ->arrayNode('cache')->addDefaultsIfNotSet()
          ->children()
            ->booleanNode('enabled')->defaultValue(FALSE)->end()
            ->scalarNode('seconds')->defaultValue(60)->end()
            ->scalarNode('prefix')->defaultValue('datagrid')->end()
          ->end()
        ->end()
        ->arrayNode('pagination')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('template')->defaultValue('HBMDatagridBundle:Pagination:pagination.html.twig')->end()
            ->scalarNode('max_links_per_page')->defaultValue(10)->end()
            ->booleanNode('show_first')->defaultTrue()->end()
            ->booleanNode('show_prev')->defaultTrue()->end()
            ->booleanNode('show_next')->defaultTrue()->end()
            ->booleanNode('show_last')->defaultTrue()->end()
            ->booleanNode('show_sep')->defaultTrue()->end()
          ->end()
        ->end()
        ->arrayNode('menu')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('template')->defaultValue('HBMDatagridBundle:Menu:navbar.html.twig')->end()
            ->scalarNode('show')->defaultTrue()->end()
            ->scalarNode('show_search')->defaultTrue()->end()
            ->arrayNode('search_fields')
              ->prototype('array')
                ->children()
                  ->scalarNode('type')->defaultValue('text')->end()
                  ->scalarNode('label')->defaultValue('Suche')->end()
                ->end()
              ->end()
            ->end()
            ->scalarNode('show_reset')->defaultTrue()->end()
            ->scalarNode('show_extended')->defaultTrue()->end()
            ->scalarNode('show_range')->defaultTrue()->end()
            ->scalarNode('show_header')->defaultTrue()->end()
            ->scalarNode('show_max_entries_selection')->defaultTrue()->end()
            ->arrayNode('max_entries_selection')
              ->prototype('scalar')->end()
              ->defaultValue(array(10, 20, 50, 100))
            ->end()
          ->end()
        ->end()
      ->end()
    ->end();

    return $treeBuilder;
  }

}
