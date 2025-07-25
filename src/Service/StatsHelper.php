<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Service;

use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Svc\LogBundle\Settings\SvcLogSettings;

/**
 * Helper class for creating statistics.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class StatsHelper
{
    public function __construct(
        private readonly SvcLogStatMonthlyRepository $statMonRep,
        private readonly SettingsManagerInterface $settingsManager,
    ) {
    }

    /**
     * aggregate data for monthly statistics.
     *
     * @param bool $fresh should the data reloaded completely (truncate table before)
     *
     * @return array<mixed>
     */
    public function aggrMonthly(bool $fresh = false): array
    {
        $logSettings = $this->settingsManager->get(SvcLogSettings::class);
        if ($fresh) {
            $lastRun = null;
        } else {
            $lastRun = $logSettings->getLastRunAggrMonthly();
        }

        $firstDay = $lastRun ? new \DateTime($lastRun->format('Y-m-01')) : null;

        if ($fresh) {
            $this->statMonRep->truncateStatMonthlyTable();
            $deleted = 0;
        } else {
            $deleted = $this->statMonRep->deleteCurrentData($firstDay);
        }

        $inserted = $this->statMonRep->aggrData($firstDay);

        $logSettings->setLastRunAggrMonthlyToNow();
        $this->settingsManager->save($logSettings);

        return ['deleted' => $deleted, 'inserted' => $inserted];
    }
}
