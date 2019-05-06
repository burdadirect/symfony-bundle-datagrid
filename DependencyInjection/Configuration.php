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
    $treeBuilder = new TreeBuilder('hbm_datagrid');

    if (method_exists($treeBuilder, 'getRootNode')) {
      $rootNode = $treeBuilder->getRootNode();
    } else {
      $rootNode = $treeBuilder->root('hbm_datagrid');
    }

    $rootNode
      ->children()
        ->scalarNode('translation_domain')->defaultValue(false)->end()
        ->arrayNode('bootstrap')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('version')->defaultValue('v4')->end()
              ->arrayNode('sizes')->addDefaultsIfNotSet()
                ->children()
                  ->scalarNode('btn')->defaultValue('')->end()
                  ->scalarNode('btn_group')->defaultValue('')->end()
                  ->scalarNode('input_group')->defaultValue('')->end()
                  ->scalarNode('table')->defaultValue('')->end()
                  ->scalarNode('pagination')->defaultValue('')->end()
              ->end()
            ->end()
            ->arrayNode('classes')->addDefaultsIfNotSet()
              ->children()
                ->scalarNode('btn')->defaultValue('btn btn-secondary')->end()
                ->scalarNode('btn_group')->defaultValue('btn-group')->end()
                ->scalarNode('input_group')->defaultValue('input-group')->end()
                ->scalarNode('navbar')->defaultValue('navbar navbar-light bg-light navbar-expand-sm mb-3')->end()
                ->scalarNode('table')->defaultValue('table table-hover table-bordered')->end()
                ->scalarNode('pagination')->defaultValue('pagination justify-content-center')->end()
                ->scalarNode('page_item')->defaultValue('page-item')->end()
                ->scalarNode('page_link')->defaultValue('page-link')->end()
              ->end()
            ->end()
            ->arrayNode('icons')->addDefaultsIfNotSet()
              ->children()
                // Sortation
                ->scalarNode('sort_asc')->defaultValue('fa fa-sort-amount-asc')->end()
                ->scalarNode('sort_desc')->defaultValue('fa fa-sort-amount-desc')->end()
                // Navigation
                ->scalarNode('search')->defaultValue('fa fa-search')->end()
                ->scalarNode('reset')->defaultValue('fa fa-bolt')->end()
                ->scalarNode('expand')->defaultValue('fa fa-expand')->end()
                ->scalarNode('compress')->defaultValue('fa fa-compress')->end()
                // Pagination
                ->scalarNode('page_first')->defaultValue('fa fa-fast-backward')->end()
                ->scalarNode('page_prev')->defaultValue('fa fa-step-backward')->end()
                ->scalarNode('page_next')->defaultValue('fa fa-step-forward')->end()
                ->scalarNode('page_last')->defaultValue('fa fa-fast-forward')->end()
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('session')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('prefix')->defaultValue('hbm_datagrid:')->end()
            ->arrayNode('use_for')
              ->prototype('scalar')->end()
              ->defaultValue(['num', 'sort', 'extended'])
            ->end()
          ->end()
        ->end()
        ->arrayNode('query')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('encode')->defaultValue('json')->end()
            ->arrayNode('param_names')->addDefaultsIfNotSet()
              ->children()
                ->scalarNode('current_page')->defaultValue('page')->end()
                ->scalarNode('max_entries')->defaultValue('num')->end()
                ->scalarNode('sortation')->defaultValue('sort')->end()
                ->scalarNode('search')->defaultValue('search')->end()
                ->scalarNode('extended')->defaultValue('extended')->end()
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
            ->scalarNode('template')->defaultValue('@HBMDatagrid/Pagination/pagination.html.twig')->end()
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
            ->scalarNode('template')->defaultValue('@HBMDatagrid/Menu/navbar.html.twig')->end()
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
            ->scalarNode('show_export')->defaultTrue()->end()
            ->scalarNode('show_range')->defaultTrue()->end()
            ->scalarNode('show_header')->defaultTrue()->end()
            ->scalarNode('show_max_entries_selection')->defaultTrue()->end()
            ->arrayNode('exports_selection')
              ->prototype('scalar')->end()
              ->defaultValue(['csv', 'xlsx', 'json'])
            ->end()
            ->arrayNode('exports_resources')->defaultValue(['max_execution_time' => 60])->useAttributeAsKey('key')
              ->prototype('array')
                ->children()
                  ->scalarNode('value')->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('max_entries_selection')
              ->prototype('scalar')->end()
              ->defaultValue([10, 20, 50, 100, 250])
            ->end()
          ->end()
        ->end()
      ->end()
    ->end();

    return $treeBuilder;
  }

}
