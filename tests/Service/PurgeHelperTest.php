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

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\PurgeHelper;

#[AllowMockObjectsWithoutExpectations]
class PurgeHelperTest extends TestCase
{
    private PurgeHelper $purgeHelper;

    private SvcLogRepository&MockObject $svcLogRep;

    protected function setUp(): void
    {
        $this->svcLogRep = $this->createMock(SvcLogRepository::class);
        $this->purgeHelper = new PurgeHelper($this->svcLogRep);
    }

    public function testPurgeLogsDryRun(): void
    {
        $this->svcLogRep->expects($this->once())
            ->method('purgeOldData')
            ->with(
                $this->callback(function ($date) {
                    return $date instanceof \DateTime;
                }),
                true
            )
            ->willReturn(50);

        $result = $this->purgeHelper->purgeLogs(6, true);
        $this->assertEquals(50, $result);
    }

    public function testPurgeLogsActualPurge(): void
    {
        $this->svcLogRep->expects($this->once())
            ->method('purgeOldData')
            ->with(
                $this->callback(function ($date) {
                    return $date instanceof \DateTime;
                }),
                false
            )
            ->willReturn(25);

        $result = $this->purgeHelper->purgeLogs(12, false);
        $this->assertEquals(25, $result);
    }

    public function testPurgeLogsCalculatesCorrectDate(): void
    {
        $today = new \DateTime();
        $expectedFirstDay = new \DateTime($today->format('Y-m-01'));
        $expectedFirstDay->sub(new \DateInterval('P3M'));

        $this->svcLogRep->expects($this->once())
            ->method('purgeOldData')
            ->with(
                $this->callback(function ($date) use ($expectedFirstDay) {
                    return $date instanceof \DateTime
                        && $date->format('Y-m-d') === $expectedFirstDay->format('Y-m-d');
                }),
                $this->anything()
            )
            ->willReturn(10);

        $result = $this->purgeHelper->purgeLogs(3, false);
        $this->assertEquals(10, $result);
    }

    public function testPurgeLogsWithZeroMonths(): void
    {
        $today = new \DateTime();
        $expectedFirstDay = new \DateTime($today->format('Y-m-01'));

        $this->svcLogRep->expects($this->once())
            ->method('purgeOldData')
            ->with(
                $this->callback(function ($date) use ($expectedFirstDay) {
                    return $date instanceof \DateTime
                        && $date->format('Y-m-d') === $expectedFirstDay->format('Y-m-d');
                }),
                false
            )
            ->willReturn(0);

        $result = $this->purgeHelper->purgeLogs(0, false);
        $this->assertEquals(0, $result);
    }

    public function testPurgeLogsWithLargeMonthValue(): void
    {
        $this->svcLogRep->expects($this->once())
            ->method('purgeOldData')
            ->with(
                $this->callback(function ($date) {
                    // Should be many months ago
                    $today = new \DateTime();

                    return $date instanceof \DateTime && $date < $today;
                }),
                true
            )
            ->willReturn(1000);

        $result = $this->purgeHelper->purgeLogs(24, true);
        $this->assertEquals(1000, $result);
    }
}
