<?php

namespace Svc\LogBundle\Service;

use DateInterval;
use DateTime;
use Svc\LogBundle\Exception\IpSavingNotEnabledException;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Helper class for displaing statistics
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class LogStatistics
{

  private $svcLogRep;
  private $requestStack;
  private $router;
  private $enableSourceType;
  private $enableIPSaving;
  private $offsetParamName;
  private $statMonRep;

  public function __construct(
    bool $enableSourceType,
    bool $enableIPSaving,
    string $offsetParamName,
    SvcLogRepository $svcLogRep,
    SvcLogStatMonthlyRepository $statMonRep,
    RequestStack $requestStack,
    UrlGeneratorInterface $router
  ) {
    $this->enableSourceType = $enableSourceType;
    $this->enableIPSaving = $enableIPSaving;
    $this->offsetParamName = $offsetParamName;
    $this->svcLogRep = $svcLogRep;
    $this->statMonRep = $statMonRep;
    $this->requestStack = $requestStack;
    $this->router = $router;
  }

  /**
   * give an array with log entries for one sourceID
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @return array
   */
  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array
  {
    $request = $this->requestStack->getCurrentRequest();
    $offset = $request->query->get($this->offsetParamName) ?? 0;

    $logEntries = $this->svcLogRep->getLogPaginator($offset, $sourceID, $sourceType, $logLevel);
    if (count($logEntries) == 0) {
      return [];
    }

    if ($offset >= count($logEntries)) {
      $offset = count($logEntries) - SvcLogRepository::PAGINATOR_PER_PAGE;
      $logEntries = $this->svcLogRep->getLogPaginator($offset, $sourceID, $sourceType, $logLevel);
    }

    $routeName = $request->attributes->get('_route');
    $defRouteParam = $request->attributes->get('_route_params');

    $firstUrl = $this->router->generate($routeName, $defRouteParam);

    $prevRoutParam = $defRouteParam;
    $prevRoutParam[$this->offsetParamName] = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $prevUrl = $this->router->generate($routeName, $prevRoutParam);

    $nextRoutParam = $defRouteParam;
    $nextRoutParam[$this->offsetParamName] = min(count($logEntries), $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
    $nextUrl = $this->router->generate($routeName, $nextRoutParam);

    $lastRoutParam = $defRouteParam;
    $lastRoutParam[$this->offsetParamName] = count($logEntries) - SvcLogRepository::PAGINATOR_PER_PAGE;
    $lastUrl = $this->router->generate($routeName, $lastRoutParam);

    $data = [];

    $data['records'] = $logEntries;
    $data['nextUrl'] = $nextUrl;
    $data['prevUrl'] = $prevUrl;
    $data['firstUrl'] = $firstUrl;
    $data['lastUrl'] = $lastUrl;
    $data['offset'] = $offset;
    $data['count'] = count($logEntries);
    $data['hidePrev'] = $offset <= 0;
    $data['hideNext'] = $offset >= count($logEntries) - SvcLogRepository::PAGINATOR_PER_PAGE;
    $data['from'] = $offset + 1;
    $data['to'] = min($offset + SvcLogRepository::PAGINATOR_PER_PAGE, count($logEntries));
    return $data;
  }


  /**
   * pivot the data for a specific sourceType for the last 5 month
   *
   * @param integer $sourceType
   * @param integer|null $logLevel
   * @return array
   */
  public function pivotMonthly(int $sourceType, ?int $logLevel = EventLog::LEVEL_ALL): array
  {

    $today = new DateTime();
    $firstDay = new DateTime($today->format('Y-m-01'));

    $oneMonth = new DateInterval("P1M");
    for ($i = 1; $i <= 5; $i++) {
      $monthList[] = $firstDay->format('Y-m');
      $firstDay = $firstDay->sub($oneMonth);
    }

    $data['header'] = $monthList;
    $data['data'] = $this->statMonRep->pivotData($monthList, $sourceType, $logLevel);
    return $data;
  }

  /**
   * get an array with countries and counts/country for an specific sourceID
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @return array
   * @throws LogExceptionInterface
   */
  public function getCountriesForOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array
  {
    if (!$this->enableIPSaving) {
      throw new IpSavingNotEnabledException();
    }
    return $this->svcLogRep->aggrLogsByCountry($sourceID, $sourceType, $logLevel);
  }


  /**
   * format counts/country for ChartJS
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @param integer|null $maxEntries
   * @return array
   * @throws LogExceptionInterface
   */
  public function getCountriesForChartJS(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA, ?int $maxEntries = 5): array
  {
    if (!$this->enableIPSaving) {
      throw new IpSavingNotEnabledException();
    }
    $chartLabels = [];
    $chartData = [];

    $counter = 0;
    foreach ($this->getCountriesForOneId($sourceID, $sourceType, $logLevel) as $values) {
      $chartLabels[] = $values['country'] ?? "?";
      $chartData[] =  $values['cntCountry'];
      $counter++;
      if ($counter == $maxEntries) {
        break;
      }
    }

    $result["labels"] = $chartLabels;
    $result["datasets"][0]["data"] = $chartData;
    return $result;
  }
}
