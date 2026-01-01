<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Svc\LogBundle\Command\BatchFillLocationCommand;
use Svc\LogBundle\Command\MailDailySummary;
use Svc\LogBundle\Command\PurgeLogsCommand;
use Svc\LogBundle\Command\StatMonthlyCommand;
use Svc\LogBundle\Controller\DailySummaryController;
use Svc\LogBundle\Controller\LogViewerController;
use Svc\LogBundle\DataProvider\GeneralDataProvider;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\EventListener\HttpExceptionListener;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Svc\LogBundle\Service\BatchHelper;
use Svc\LogBundle\Service\DailySummaryHelper;
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Service\LoggerHelper;
use Svc\LogBundle\Service\LogStatistics;
use Svc\LogBundle\Service\PurgeHelper;
use Svc\LogBundle\Service\StatsHelper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    // Services
    $services->set(EventLog::class);
    $services->set(LogStatistics::class);
    $services->set(StatsHelper::class);
    $services->set(LoggerHelper::class);
    $services->set(PurgeHelper::class);
    $services->set(BatchHelper::class);
    $services->set(DailySummaryHelper::class)
        ->args([service(GeneralDataProvider::class)]);

    // Data Providers
    $services->set(GeneralDataProvider::class);

    // Commands
    $services->set(BatchFillLocationCommand::class);
    $services->set(StatMonthlyCommand::class);
    $services->set(PurgeLogsCommand::class);
    $services->set(MailDailySummary::class);

    // Repositories
    $services->set(SvcLogRepository::class);
    $services->set(SvcLogStatMonthlyRepository::class);

    // Controllers
    $services->set(LogViewerController::class)
        ->args([service(GeneralDataProvider::class)]);
    $services->set(DailySummaryController::class);

    // Entities
    $services->set(DailySumDef::class);

    // Event Listeners
    $services->set(HttpExceptionListener::class);
};
