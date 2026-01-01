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

namespace Svc\LogBundle\Tests\Service;

use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Svc\LogBundle\Service\StatsHelper;
use Svc\LogBundle\Settings\SvcLogSettings;

#[AllowMockObjectsWithoutExpectations]
class StatsHelperTest extends TestCase
{
    private StatsHelper $statsHelper;

    private SvcLogStatMonthlyRepository&MockObject $statMonRep;

    private SettingsManagerInterface&MockObject $settingsManager;

    private SvcLogSettings&MockObject $settings;

    protected function setUp(): void
    {
        $this->statMonRep = $this->createMock(SvcLogStatMonthlyRepository::class);
        $this->settingsManager = $this->createMock(SettingsManagerInterface::class);
        $this->settings = $this->createMock(SvcLogSettings::class);

        $this->statsHelper = new StatsHelper(
            $this->statMonRep,
            $this->settingsManager
        );
    }

    public function testAggrMonthlyWithFreshData(): void
    {
        $this->settingsManager->expects($this->once())
            ->method('get')
            ->with(SvcLogSettings::class)
            ->willReturn($this->settings);

        $this->statMonRep->expects($this->once())
            ->method('truncateStatMonthlyTable');

        $this->statMonRep->expects($this->never())
            ->method('deleteCurrentData');

        $this->statMonRep->expects($this->once())
            ->method('aggrData')
            ->with(null)
            ->willReturn(10);

        $this->settings->expects($this->once())
            ->method('setLastRunAggrMonthlyToNow');

        $this->settingsManager->expects($this->once())
            ->method('save')
            ->with($this->settings);

        $result = $this->statsHelper->aggrMonthly(true);

        $this->assertEquals(['deleted' => 0, 'inserted' => 10], $result);
    }

    public function testAggrMonthlyWithIncrementalUpdate(): void
    {
        $lastRun = new \DateTime('2024-12-15 10:30:00');
        $expectedFirstDay = new \DateTime('2024-12-01');

        $this->settingsManager->expects($this->once())
            ->method('get')
            ->with(SvcLogSettings::class)
            ->willReturn($this->settings);

        $this->settings->expects($this->once())
            ->method('getLastRunAggrMonthly')
            ->willReturn($lastRun);

        $this->statMonRep->expects($this->never())
            ->method('truncateStatMonthlyTable');

        $this->statMonRep->expects($this->once())
            ->method('deleteCurrentData')
            ->with($this->callback(function ($date) use ($expectedFirstDay) {
                return $date instanceof \DateTime && $date->format('Y-m-d') === $expectedFirstDay->format('Y-m-d');
            }))
            ->willReturn(5);

        $this->statMonRep->expects($this->once())
            ->method('aggrData')
            ->with($this->callback(function ($date) use ($expectedFirstDay) {
                return $date instanceof \DateTime && $date->format('Y-m-d') === $expectedFirstDay->format('Y-m-d');
            }))
            ->willReturn(15);

        $this->settings->expects($this->once())
            ->method('setLastRunAggrMonthlyToNow');

        $this->settingsManager->expects($this->once())
            ->method('save')
            ->with($this->settings);

        $result = $this->statsHelper->aggrMonthly(false);

        $this->assertEquals(['deleted' => 5, 'inserted' => 15], $result);
    }

    public function testAggrMonthlyWithNoLastRun(): void
    {
        $this->settingsManager->expects($this->once())
            ->method('get')
            ->with(SvcLogSettings::class)
            ->willReturn($this->settings);

        $this->settings->expects($this->once())
            ->method('getLastRunAggrMonthly')
            ->willReturn(null);

        $this->statMonRep->expects($this->never())
            ->method('truncateStatMonthlyTable');

        $this->statMonRep->expects($this->once())
            ->method('deleteCurrentData')
            ->with(null)
            ->willReturn(0);

        $this->statMonRep->expects($this->once())
            ->method('aggrData')
            ->with(null)
            ->willReturn(20);

        $this->settings->expects($this->once())
            ->method('setLastRunAggrMonthlyToNow');

        $this->settingsManager->expects($this->once())
            ->method('save')
            ->with($this->settings);

        $result = $this->statsHelper->aggrMonthly(false);

        $this->assertEquals(['deleted' => 0, 'inserted' => 20], $result);
    }
}
