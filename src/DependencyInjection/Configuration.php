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
        ->booleanNode('enableSourceType')->defaultTrue()->info('Do you like different source types?')->end()
      ->end();
    return $treeBuilder;

  }

}