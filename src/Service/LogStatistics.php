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

  public function __construct(
      private bool $enableSourceType,  /** @phpstan-ignore-line */
      private bool $enableIPSaving,
      private string $offsetParamName,
      private SvcLogRepository $svcLogRep,
      private SvcLogStatMonthlyRepository $statMonRep,
      private RequestStack $requestStack,
      private UrlGeneratorInterface $router
  )
  {
  }

  /**
   * give an array with log entries for one sourceID
   */
  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array
  {
    $request = $this->requestStack->getCurrentRequest();
    $offset = $request->query->get($this->offsetParamName) ?? 0;

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
   */
  public function pivotMonthly(int $sourceType, ?int $logLevel = EventLog::LEVEL_ALL): array
  {

    $today = new DateTime();
    $firstDay = new DateTime($today->format('Y-m-01'));

    $oneMonth = new DateInterval("P1M");
    $monthList = [];
    for ($i = 1; $i <= 5; $i++) {
      $monthList[] = $firstDay->format('Y-m');
      $firstDay = $firstDay->sub($oneMonth);
    }

    $data = [];
    $data['header'] = $monthList;
    $data['data'] = $this->statMonRep->pivotData($monthList, $sourceType, $logLevel);
    return $data;
  }

  /**
   * get an array with countries and counts/country for an specific sourceID
   *
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
   * format counts/country for symfony/ux-chartjs
   *
   * @param integer|null $sourceType (Default 0)
   * @param integer|null $logLevel (Default DATA)
   * @param integer|null $maxEntries (Default 5)
   * @throws LogExceptionInterface
   */
  public function getCountriesForChartJS(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA, ?int $maxEntries = 5): array
  {
    $result = [];
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

  /**
   * format counts/country as array for direct chart.js integration per yarn
   *
   * @throws LogExceptionInterface
   */
  public function getCountriesForChartJS1(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA, ?int $maxEntries = 5): array
  {
    $results = [];
    if (!$this->enableIPSaving) {
      throw new IpSavingNotEnabledException();
    }
    $chartArray = $this->getCountriesForChartJS($sourceID, $sourceType, $logLevel, $maxEntries);
    $results["labels"] = implode("|", $chartArray["labels"]);
    $results["data"] = implode("|", $chartArray["datasets"][0]["data"]);
    return $results;
  }

}
