<?php

namespace Svc\LogBundle;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SvcLogBundle extends AbstractBundle
{
  public function getPath(): string
  {
    return \dirname(__DIR__);
  }

  public function configure(DefinitionConfigurator $definition): void
  {
    $definition->rootNode()
      ->children()
        ->integerNode('min_log_level')->min(0)->max(6)->defaultValue(1)->info('Minimal log level, see documentation for values')->end()
        ->booleanNode('enable_ip_saving')->defaultFalse()->info('Should the ip address recorded? Please set to true only if this is allowed in your environment (personal data...)')->end()
        ->booleanNode('enable_user_saving')->defaultFalse()->info('Should the user id and name recorded? Please set to true only if this is allowed in your environment (personal data...)')->end()
        ->booleanNode('enable_source_type')->defaultTrue()->info('Do you like different source types?')->end()
        ->booleanNode('need_admin_for_view')->defaultTrue()->info('Need the user the role ROLE_ADMIN for viewing logs (default yes)')->end()
        ->booleanNode('need_admin_for_stats')->defaultTrue()->info('Need the user the role ROLE_ADMIN for get statistics (default yes)')->end()
        ->scalarNode('offset_param_name')->cannotBeEmpty()->defaultValue('offset')->info('We use offset as url parameter. If this in use, you can choose another name')->end()
        ->scalarNode('data_provider')->defaultNull()->info('Class of your one data provider to get info about sourceType and sourceID, see documentation')->end()
        ->arrayNode('sentry')->addDefaultsIfNotSet()->info('Optional configuration for sentry.io, see documentation')
          ->children()
            ->scalarNode('use_sentry')->defaultFalse()->info('Write log entries to sentry.io too')->end()
            ->integerNode('sentry_min_log_level')->min(4)->max(6)->defaultValue(6)->info('Minimal log level to write to sentry, see documentation for values (only 4..6 allowed)')->end()
          ->end()
        ->end()
        ->arrayNode('logger')->addDefaultsIfNotSet()->info('Optional configuration for default logger, see documentation')
          ->children()
            ->scalarNode('use_logger')->defaultFalse()->info('Write log entries to default logger too')->end()
            ->integerNode('logger_min_log_level')->min(3)->max(6)->defaultValue(6)->info('Minimal log level to write to logger, see documentation for values (only 3..6 allowed)')->end()
          ->end()
        ->end()
        ->arrayNode('daily_summary')->addDefaultsIfNotSet()->info('Definition of the daily summary report')
          ->children()
            ->scalarNode('definition_class')->defaultNull()->info('Class of your daily summary definition')->end()
            ->scalarNode('destination_email')->defaultNull()->info('Mail address for the daily summary mail')->end()
            ->scalarNode('mail_subject')->defaultValue('Daily summary')->cannotBeEmpty()->info('Subject of the daily summary mail')->end()
          ->end()
        ->end()
        ->arrayNode('kernel_exception_logger')->addDefaultsIfNotSet()->info('Configuration for the (optional) kernel exception logger')
          ->children()
            ->scalarNode('use_kernel_logger')->defaultFalse()->info('enable the kernel exception logger')->end()
            ->integerNode('default_level')->min(4)->max(8)->defaultValue(5)->info('Default log level (only 4..8 allowed)')->end()
            ->integerNode('critical_level')->min(4)->max(8)->defaultValue(6)->info('Log level for critical errors - http code 500 (only 4..8 allowed)')->end()
            ->scalarNode('disable_404_to_logger')->defaultFalse()->info('do not send http 404 to logger (and maybe mail)')->end()
            ->integerNode('extra_sleep_time')->min(0)->max(5)->defaultValue(0)->info('adds an extra sleep after every 404 error (in seconds, max. 5)')->end()
          ->end()
        ->end()

       ->end();
  }

  /**
   * @param array<mixed> $config
   */
  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
  {
    $container->import('../config/services.yaml');

    (bool) $enableUserSaving = $config['enable_user_saving'];
    // if ($enableUserSaving) {
    //   if (!array_key_exists('SecurityBundle', $builder->getParameter('kernel.bundles'))) {
    //     throw new \Exception('If you set "enable_user_saving" to true (in svc_log.yaml) you have to install the SecurityBundle.');
    //   }
    // }

    (bool) $enableSentry = $config['sentry']['use_sentry'];
    if ($enableSentry) {
      if (!array_key_exists('SentryBundle', (array) $builder->getParameter('kernel.bundles'))) {
        throw new \Exception('If you enable sentry (in svc_log.yaml) you have to install the SentryBundle.');
      }
    }

    $container->services()
      ->get('Svc\LogBundle\Service\LogStatistics')
      ->arg(0, $config['enable_source_type'])
      ->arg(1, $config['enable_ip_saving'])
      ->arg(2, $config['offset_param_name'])
      ->arg(3, $config['need_admin_for_stats']);

    $container->services()
      ->get('Svc\LogBundle\Service\EventLog')
      ->arg(0, $config['enable_source_type'])
      ->arg(1, $config['enable_ip_saving'])
      ->arg(2, $enableUserSaving)
      ->arg(3, $config['min_log_level'])
      ->arg(4, $enableSentry)
      ->arg(5, $config['sentry']['sentry_min_log_level'])
      ->arg(6, $config['logger']['use_logger'])
      ->arg(7, $config['logger']['logger_min_log_level'])
      ->arg(8, $config['kernel_exception_logger']['disable_404_to_logger'])
    ;

    $container->services()
      ->get('Svc\LogBundle\Controller\LogViewerController')
      ->arg(1, $enableUserSaving)
      ->arg(2, $config['enable_ip_saving'])
      ->arg(3, $config['need_admin_for_view']);

    if (null !== $config['data_provider']) {
      $builder->setAlias('Svc\LogBundle\DataProvider\GeneralDataProvider', $config['data_provider']);
    }

    $container->services()
      ->get('Svc\LogBundle\Service\DailySummaryHelper')
      ->arg(6, $config['daily_summary']['mail_subject'])
      ->arg(7, $config['daily_summary']['definition_class'])
      ->arg(8, $config['daily_summary']['destination_email'])
    ;

    $container->services()
    ->get('Svc\LogBundle\EventListener\HttpExceptionListener')
    ->arg(1, $config['kernel_exception_logger']['use_kernel_logger'])
    ->arg(2, $config['kernel_exception_logger']['default_level'])
    ->arg(3, $config['kernel_exception_logger']['critical_level'])
    ->arg(4, $config['kernel_exception_logger']['extra_sleep_time'])
    ;
  }

  public function prependExtension(ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
  {
    if (!$this->isAssetMapperAvailable($containerBuilder)) {
      return;
    }

    $containerBuilder->prependExtensionConfig('framework', [
      'asset_mapper' => [
        'paths' => [
          __DIR__ . '/../assets/src' => '@svc/log-bundle',
        ],
      ],
    ]);
  }

  private function isAssetMapperAvailable(ContainerBuilder $container): bool
  {
    if (!interface_exists(AssetMapperInterface::class)) {
      return false;
    }

    // check that FrameworkBundle 6.3 or higher is installed
    $bundlesMetadata = (array) $container->getParameter('kernel.bundles_metadata');
    if (!isset($bundlesMetadata['FrameworkBundle'])) {
      return false;
    }

    return is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
  }
}
