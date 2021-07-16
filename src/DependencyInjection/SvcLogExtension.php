<?php

namespace Svc\LogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SvcLogExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

    $definition = $container->getDefinition('Svc\LogBundle\Service\LogStatistics');
    $definition->setArgument(0, $config['enable_source_type']);
    $definition->setArgument(1, $config['offset_param_name']);
  }

}
