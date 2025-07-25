<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Settings;

use Jbtronics\SettingsBundle\ParameterTypes\DatetimeType;
use Jbtronics\SettingsBundle\Settings\Settings;
use Jbtronics\SettingsBundle\Settings\SettingsParameter;
use Jbtronics\SettingsBundle\Settings\SettingsTrait;

#[Settings()]
class SvcLogSettings
{
    use SettingsTrait;

    #[SettingsParameter(type: DatetimeType::class, label: 'SvcLog - last run of the statistic aggregation', description: 'date/time of the last monthly aggregation.', formOptions: ['disabled' => true])]
    private ?\DateTime $lastRunAggrMonthly = null;

    #[SettingsParameter(type: DatetimeType::class, label: 'SvcLog - last run of the daily summary mail', description: 'date/time of the last daily summary.', formOptions: ['disabled' => true])]
    private ?\DateTime $lastRunDailySummary = null;

    public function getLastRunAggrMonthly(): ?\DateTime
    {
        return $this->lastRunAggrMonthly;
    }

    public function setLastRunAggrMonthlyToNow(): \DateTime
    {
        $this->lastRunAggrMonthly = new \DateTime();

        return $this->lastRunAggrMonthly;
    }

    public function getLastRunDailySummary(): ?\DateTime
    {
        return $this->lastRunDailySummary;
    }

    public function setLastRunDailySummaryToNow(): \DateTime
    {
        $this->lastRunDailySummary = new \DateTime();

        return $this->lastRunDailySummary;
    }
}
