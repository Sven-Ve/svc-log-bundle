<?php

namespace Svc\LogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SvcParamExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

//    $definition = $container->getDefinition('svc_param.controller');
//    $definition->setArgument(0, $config['debug']);
  }

}
