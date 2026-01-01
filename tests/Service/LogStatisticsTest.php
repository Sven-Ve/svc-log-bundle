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
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Svc\LogBundle\Service\LogStatistics;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AllowMockObjectsWithoutExpectations]
class LogStatisticsTest extends TestCase
{
    private LogStatistics $logStatistics;

    private SvcLogRepository&MockObject $svcLogRep;

    private SvcLogStatMonthlyRepository&MockObject $statMonRep;

    private RequestStack $requestStack;

    private UrlGeneratorInterface $router;

    private Security&MockObject $security;

    protected function setUp(): void
    {
        $this->svcLogRep = $this->createMock(SvcLogRepository::class);
        $this->statMonRep = $this->createMock(SvcLogStatMonthlyRepository::class);
        $this->requestStack = $this->createStub(RequestStack::class);
        $this->router = $this->createStub(UrlGeneratorInterface::class);
        $this->security = $this->createMock(Security::class);

        $this->logStatistics = new LogStatistics(
            enableSourceType: true,
            enableIPSaving: true,
            offsetParamName: 'offset',
            needAdminForStats: false,
            svcLogRep: $this->svcLogRep,
            statMonRep: $this->statMonRep,
            requestStack: $this->requestStack,
            router: $this->router,
            security: $this->security
        );
    }

    public function testReportOneIdReturnsEmptyArrayWhenAdminRequired(): void
    {
        $logStatistics = new LogStatistics(
            enableSourceType: true,
            enableIPSaving: true,
            offsetParamName: 'offset',
            needAdminForStats: true,
            svcLogRep: $this->svcLogRep,
            statMonRep: $this->statMonRep,
            requestStack: $this->requestStack,
            router: $this->router,
            security: $this->security
        );

        $this->security->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $result = $logStatistics->reportOneId(123);
        $this->assertEquals([], $result);
    }

    public function testPivotMonthlyReturnsEmptyDataWhenAdminRequired(): void
    {
        $logStatistics = new LogStatistics(
            enableSourceType: true,
            enableIPSaving: true,
            offsetParamName: 'offset',
            needAdminForStats: true,
            svcLogRep: $this->svcLogRep,
            statMonRep: $this->statMonRep,
            requestStack: $this->requestStack,
            router: $this->router,
            security: $this->security
        );

        $this->security->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $result = $logStatistics->pivotMonthly(1);

        $this->assertArrayHasKey('header', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals([], $result['data']);
        $this->assertCount(5, $result['header']); // 5 months
    }

    public function testPivotMonthlyReturnsDataWithoutDailyStats(): void
    {
        $mockData = [
            ['sourceID' => 1, 'month0' => 10, 'month1' => 5],
            ['sourceID' => 2, 'month0' => 8, 'month1' => 3],
        ];

        $this->statMonRep->expects($this->once())
            ->method('pivotData')
            ->willReturn($mockData);

        $result = $this->logStatistics->pivotMonthly(1, null, false);

        $this->assertArrayHasKey('header', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($mockData, $result['data']);
        $this->assertCount(5, $result['header']);
    }

    public function testPivotMonthlyReturnsDataWithDailyStats(): void
    {
        $mockData = [
            ['sourceID' => 1, 'month0' => 10, 'month1' => 5, 'month2' => 3, 'month3' => 2, 'month4' => 1],
            ['sourceID' => 2, 'month0' => 8, 'month1' => 3, 'month2' => 1, 'month3' => 0, 'month4' => 0],
        ];

        $dailyStats = [1 => 5, 2 => 2];

        $this->statMonRep->expects($this->once())
            ->method('pivotData')
            ->willReturn($mockData);

        $this->svcLogRep->expects($this->once())
            ->method('aggrLogsForCurrentDay')
            ->with(1)
            ->willReturn($dailyStats);

        $result = $this->logStatistics->pivotMonthly(1, null, true);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total5', $result['data'][0]);
        $this->assertArrayHasKey('daily', $result['data'][0]);
        $this->assertEquals(21, $result['data'][0]['total5']); // 10+5+3+2+1
        $this->assertEquals(5, $result['data'][0]['daily']);
        $this->assertEquals(12, $result['data'][1]['total5']); // 8+3+1+0+0
        $this->assertEquals(2, $result['data'][1]['daily']);
    }

    public function testGetCountriesForOneIdThrowsExceptionWhenIPSavingDisabled(): void
    {
        $logStatistics = new LogStatistics(
            enableSourceType: true,
            enableIPSaving: false,
            offsetParamName: 'offset',
            needAdminForStats: false,
            svcLogRep: $this->svcLogRep,
            statMonRep: $this->statMonRep,
            requestStack: $this->requestStack,
            router: $this->router,
            security: $this->security
        );

        $this->expectException(DeleteAllLogsForbidden::class);
        $logStatistics->getCountriesForOneId(123);
    }

    public function testGetCountriesForOneIdReturnsEmptyArrayWhenAdminRequired(): void
    {
        $logStatistics = new LogStatistics(
            enableSourceType: true,
            enableIPSaving: true,
            offsetParamName: 'offset',
            needAdminForStats: true,
            svcLogRep: $this->svcLogRep,
            statMonRep: $this->statMonRep,
            requestStack: $this->requestStack,
            router: $this->router,
            security: $this->security
        );

        $this->security->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $result = $logStatistics->getCountriesForOneId(123);
        $this->assertEquals([], $result);
    }

    public function testGetCountriesForOneIdReturnsData(): void
    {
        $mockCountries = [
            ['country' => 'CH', 'cntCountry' => 10],
            ['country' => 'DE', 'cntCountry' => 5],
        ];

        $this->svcLogRep->expects($this->once())
            ->method('aggrLogsByCountry')
            ->with(123, 0, null)
            ->willReturn($mockCountries);

        $result = $this->logStatistics->getCountriesForOneId(123);
        $this->assertEquals($mockCountries, $result);
    }

    public function testGetCountriesForChartJSReturnsFormattedData(): void
    {
        $mockCountries = [
            ['country' => 'CH', 'cntCountry' => 10],
            ['country' => 'DE', 'cntCountry' => 5],
            ['country' => 'FR', 'cntCountry' => 3],
        ];

        $this->svcLogRep->expects($this->once())
            ->method('aggrLogsByCountry')
            ->with(123, 0, null)
            ->willReturn($mockCountries);

        $result = $this->logStatistics->getCountriesForChartJS(123);

        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('datasets', $result);
        $this->assertEquals(['CH', 'DE', 'FR'], $result['labels']);
        $this->assertEquals([10, 5, 3], $result['datasets'][0]['data']);
    }

    public function testGetCountriesForChartJSLimitsEntries(): void
    {
        $mockCountries = [
            ['country' => 'CH', 'cntCountry' => 10],
            ['country' => 'DE', 'cntCountry' => 5],
            ['country' => 'FR', 'cntCountry' => 3],
            ['country' => 'IT', 'cntCountry' => 2],
            ['country' => 'ES', 'cntCountry' => 1],
        ];

        $this->svcLogRep->expects($this->once())
            ->method('aggrLogsByCountry')
            ->willReturn($mockCountries);

        $result = $this->logStatistics->getCountriesForChartJS(123, 0, null, 2);

        $this->assertCount(2, $result['labels']);
        $this->assertCount(2, $result['datasets'][0]['data']);
        $this->assertEquals(['CH', 'DE'], $result['labels']);
        $this->assertEquals([10, 5], $result['datasets'][0]['data']);
    }

    public function testGetCountriesForChartJS1ReturnsStringFormat(): void
    {
        $mockCountries = [
            ['country' => 'CH', 'cntCountry' => 10],
            ['country' => 'DE', 'cntCountry' => 5],
        ];

        $this->svcLogRep->expects($this->once())
            ->method('aggrLogsByCountry')
            ->willReturn($mockCountries);

        $result = $this->logStatistics->getCountriesForChartJS1(123);

        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('CH|DE', $result['labels']);
        $this->assertEquals('10|5', $result['data']);
    }
}
