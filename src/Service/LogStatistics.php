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

use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Helper class for displaying statistics.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class LogStatistics
{
    public function __construct(
        private bool $enableSourceType,  /** @phpstan-ignore-line */
        private readonly bool $enableIPSaving,
        private readonly string $offsetParamName,
        private readonly bool $needAdminForStats,
        private readonly SvcLogRepository $svcLogRep,
        private readonly SvcLogStatMonthlyRepository $statMonRep,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $router,
        private readonly Security $security,
    ) {
    }

    /**
     * give an array with log entries for one sourceID.
     *
     * @return array<mixed>
     */
    public function reportOneId(int $sourceID, ?int $sourceType = 0, ?LogLevel $logLevel = null): array
    {
        if ($this->needAdminForStats && !$this->security->isGranted('ROLE_ADMIN')) {
            return [];
        }

        $request = $this->requestStack->getCurrentRequest();
        $offset = $request->query->getInt($this->offsetParamName);

        $logEntries = $this->svcLogRep->getLogPaginator($offset, $sourceID, $sourceType, $logLevel);
        if ((is_countable($logEntries) ? count($logEntries) : 0) == 0) {
            return [];
        }

        if ($offset >= (is_countable($logEntries) ? count($logEntries) : 0)) {
            $offset = (is_countable($logEntries) ? count($logEntries) : 0) - SvcLogRepository::PAGINATOR_PER_PAGE;
            $logEntries = $this->svcLogRep->getLogPaginator($offset, $sourceID, $sourceType, $logLevel);
        }

        $routeName = $request->attributes->get('_route');
        $defRouteParam = $request->attributes->get('_route_params');

        $firstUrl = $this->router->generate($routeName, $defRouteParam);

        $prevRoutParam = $defRouteParam;
        $prevRoutParam[$this->offsetParamName] = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
        $prevUrl = $this->router->generate($routeName, $prevRoutParam);

        $nextRoutParam = $defRouteParam;
        $nextRoutParam[$this->offsetParamName] = min(is_countable($logEntries) ? count($logEntries) : 0, $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
        $nextUrl = $this->router->generate($routeName, $nextRoutParam);

        $lastRoutParam = $defRouteParam;
        $lastRoutParam[$this->offsetParamName] = (is_countable($logEntries) ? count($logEntries) : 0) - SvcLogRepository::PAGINATOR_PER_PAGE;
        $lastUrl = $this->router->generate($routeName, $lastRoutParam);

        $data = [];

        $data['records'] = $logEntries;
        $data['nextUrl'] = $nextUrl;
        $data['prevUrl'] = $prevUrl;
        $data['firstUrl'] = $firstUrl;
        $data['lastUrl'] = $lastUrl;
        $data['offset'] = $offset;
        $data['count'] = is_countable($logEntries) ? count($logEntries) : 0;
        $data['hidePrev'] = $offset <= 0;
        $data['hideNext'] = $offset >= (is_countable($logEntries) ? count($logEntries) : 0) - SvcLogRepository::PAGINATOR_PER_PAGE;
        $data['from'] = $offset + 1;
        $data['to'] = min($offset + SvcLogRepository::PAGINATOR_PER_PAGE, is_countable($logEntries) ? count($logEntries) : 0);

        return $data;
    }

    /**
     * pivot the data for a specific sourceType for the last 5 month
     * if access for non-admins not allowed to create the array with headers but without data.
     *
     * @return array<mixed>
     */
    public function pivotMonthly(int $sourceType, ?LogLevel $logLevel = null, ?bool $addDailyStats = false): array
    {
        $today = new \DateTime();
        $firstDay = new \DateTime($today->format('Y-m-01'));

        $oneMonth = new \DateInterval('P1M');
        $monthList = [];
        for ($i = 1; $i <= 5; ++$i) {
            $monthList[] = $firstDay->format('Y-m');
            $firstDay = $firstDay->sub($oneMonth);
        }

        $data = [];
        $data['header'] = $monthList;

        if ($this->needAdminForStats && !$this->security->isGranted('ROLE_ADMIN')) {
            $data['data'] = [];
        } else {
            $numbers = $this->statMonRep->pivotData($monthList, $sourceType, $logLevel);
            if ($addDailyStats) {
                $dailyStats = $this->svcLogRep->aggrLogsForCurrentDay($sourceType);
                foreach ($numbers as $key => $row) {
                    $numbers[$key]['total5'] = $row['month0'] + $row['month1'] + $row['month2'] + $row['month3'] + $row['month4'];
                    if (array_key_exists($row['sourceID'], $dailyStats)) {
                        $numbers[$key]['daily'] = $dailyStats[$row['sourceID']];
                    } else {
                        $numbers[$key]['daily'] = 0;
                    }
                }
            }
            $data['data'] = $numbers;
        }

        return $data;
    }

    /**
     * get an array with countries and counts/country for an specific sourceID.
     *
     * @throws LogExceptionInterface
     *
     * @return array<mixed>
     */
    public function getCountriesForOneId(int $sourceID, ?int $sourceType = 0, ?LogLevel $logLevel = null): array
    {
        if ($this->needAdminForStats && !$this->security->isGranted('ROLE_ADMIN')) {
            return [];
        }
        if (!$this->enableIPSaving) {
            throw new DeleteAllLogsForbidden();
        }

        return $this->svcLogRep->aggrLogsByCountry($sourceID, $sourceType, $logLevel);
    }

    /**
     * format counts/country for symfony/ux-chartjs.
     *
     * @param int|null      $sourceType (Default 0)
     * @param LogLevel|null $logLevel   (Default null)
     * @param int|null      $maxEntries (Default 5)
     *
     * @throws LogExceptionInterface
     *
     * @return array<mixed>
     */
    public function getCountriesForChartJS(int $sourceID, ?int $sourceType = 0, ?LogLevel $logLevel = null, ?int $maxEntries = 5): array
    {
        if ($this->needAdminForStats && !$this->security->isGranted('ROLE_ADMIN')) {
            return [];
        }

        $result = [];
        if (!$this->enableIPSaving) {
            throw new DeleteAllLogsForbidden();
        }
        $chartLabels = [];
        $chartData = [];

        $counter = 0;
        foreach ($this->getCountriesForOneId($sourceID, $sourceType, $logLevel) as $values) {
            $chartLabels[] = $values['country'] ?? '?';
            $chartData[] = $values['cntCountry'];
            ++$counter;
            if ($counter == $maxEntries) {
                break;
            }
        }

        $result['labels'] = $chartLabels;
        $result['datasets'][0]['data'] = $chartData;

        return $result;
    }

    /**
     * format counts/country as array for direct chart.js integration per yarn.
     *
     * @throws LogExceptionInterface
     *
     * @return array<mixed>
     */
    public function getCountriesForChartJS1(int $sourceID, ?int $sourceType = 0, ?LogLevel $logLevel = null, ?int $maxEntries = 5): array
    {
        if ($this->needAdminForStats && !$this->security->isGranted('ROLE_ADMIN')) {
            return [];
        }

        $results = [];
        if (!$this->enableIPSaving) {
            throw new DeleteAllLogsForbidden();
        }
        $chartArray = $this->getCountriesForChartJS($sourceID, $sourceType, $logLevel, $maxEntries);
        $results['labels'] = implode('|', $chartArray['labels']);
        $results['data'] = implode('|', $chartArray['datasets'][0]['data']);

        return $results;
    }
}
