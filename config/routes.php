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

use Svc\LogBundle\Controller\DailySummaryController;
use Svc\LogBundle\Controller\LogViewerController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('svc_log_viewer_view', '/')
        ->controller([LogViewerController::class, 'viewTable']);

    $routes->add('svc_log_viewer_view_data', '/data')
        ->controller([LogViewerController::class, 'viewData']);

    $routes->add('svc_log_viewer_view_detail', '/detail/{id}')
        ->controller([LogViewerController::class, 'viewDetail']);

    $routes->add('svc_log_daily_summary_view', '/daily_summary')
        ->controller([DailySummaryController::class, 'view']);
};
