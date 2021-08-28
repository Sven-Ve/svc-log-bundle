<?php

namespace Svc\LogBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * send configuration to classes
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class SvcLogExtension extends Extension
{
  private $rootPath;

  public function load(array $configs, ContainerBuilder $container)
  {
    $this->rootPath = $container->getParameter("kernel.project_dir");
    $this->createAssetFiles("config/packages/svc_log.yaml");
    $this->createAssetFiles("config/routes/svc_log.yaml");
    $this->createAssetFiles("config/packages/prod/svc_log.yaml");

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

    $enableUserSaving = $config['enable_user_saving'];

    if ($enableUserSaving) {
      if (!array_key_exists('SecurityBundle', $container->getParameter('kernel.bundles'))) {
        throw new Exception('if you set "enable_user_saving" to true (in svc_log.yaml) you have to install the SecurityBundle.');
      }
    }

    $definition = $container->getDefinition('Svc\LogBundle\Service\LogStatistics');
    $definition->setArgument(0, $config['enable_source_type']);
    $definition->setArgument(1, $config['enable_ip_saving']);
    $definition->setArgument(2, $config['offset_param_name']);

    $definition = $container->getDefinition('Svc\LogBundle\Service\EventLog');
    $definition->setArgument(0, $config['enable_source_type']);
    $definition->setArgument(1, $config['enable_ip_saving']);
    $definition->setArgument(2, $enableUserSaving);
    $definition->setArgument(3, $config['min_log_level']);
    if (!$enableUserSaving) {
      $definition->setArgument(4, null); // set security to null
    }

    $definition = $container->getDefinition('svc_log.controller.logviewer');
    $definition->setArgument(1, $enableUserSaving);
    $definition->setArgument(2, $config['enable_ip_saving']);

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
