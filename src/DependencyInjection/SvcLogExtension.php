<?php

namespace Svc\LogBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SvcLogExtension extends Extension
{
  private $rootPath;

  public function load(array $configs, ContainerBuilder $container)
  {
    $this->rootPath = $container->getParameter("kernel.project_dir");
    $this->createAssetFiles("config/packages/svc_log.yaml");
    $this->createAssetFiles("config/routes/svc_log.yaml");
    $this->createAssetFiles("config/packages/prod/svc_log.yaml");
    $this->createAssetFiles("assets/controllers/svcl-log-viewer_controller.js");

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

    $definition = $container->getDefinition('Svc\LogBundle\Service\LogStatistics');
    $definition->setArgument(0, $config['enable_source_type']);
    $definition->setArgument(1, $config['enable_ip_saving']);
    $definition->setArgument(2, $config['offset_param_name']);

    $definition = $container->getDefinition('Svc\LogBundle\Service\EventLog');
    $definition->setArgument(0, $config['enable_source_type']);
    $definition->setArgument(1, $config['enable_ip_saving']);
    $definition->setArgument(2, $config['min_log_level']);

    if (null !== $config['data_provider']) {
      $container->setAlias('svc_log.data_provider', $config['data_provider']);
    }

  }

  /**
   * create config and asset files
   *
   * @param string $file
   * @return boolean
   */
  private function createAssetFiles(string $file): bool
  {
    $destFile = $this->rootPath . "/" . $file;
    if (file_exists($destFile)) {
      return true;
    }
    $soureFile =  $this->rootPath . "/vendor/svc/log-bundle/install/" . $file;
    if (!file_exists($soureFile)) {
      dump("Cannot create file " . $file . " (source not exists)");
      return false;
    }

    try {
      copy($soureFile, $destFile);
    } catch (Exception $e) {
      dump("Cannot create file " . $file . " (" . $e->getMessage() . ")");
      return false;
    }
    return true;
  }
}

