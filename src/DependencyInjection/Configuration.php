<?php

namespace Svc\LogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder('svc_log');
    $rootNode = $treeBuilder->getRootNode();
 
    $rootNode
      ->children()
        ->integerNode('min_log_level')->min(0)->max(6)->defaultValue(1)->info('Minimal log level, see documentation for values')->end()
        ->booleanNode('enable_ip_saving')->defaultFalse()->info('Should the ip address recorded? Please set to true only if this is allowed in your environment (personal data...)')->end()
        ->booleanNode('enable_source_type')->defaultTrue()->info('Do you like different source types?')->end()
        ->scalarNode('offset_param_name')->cannotBeEmpty()->defaultValue('offset')->info('We use offset as url parameter. If this in use, you can choose another name')->end()      
      ->end();
    return $treeBuilder;

  }

}