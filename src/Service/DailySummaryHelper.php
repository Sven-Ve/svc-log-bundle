<?php

namespace Svc\LogBundle\Service;

use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Repository\SvcLogRepository;
use Twig\Environment;

class SummaryList
{
  /**
   * @param \Svc\LogBundle\Entity\SvcLog[] $logItems
   */
  public function __construct(public string $title, public array $logItems)
  {
  }
}

class DailySummaryHelper
{
  public function __construct(
    private readonly DataProviderInterface $dataProvider,
    private readonly Environment $twig,
    private readonly SvcLogRepository $svcLogRep)
  {
  }

  /**
   * @param DailySumDef[] $definitions
   */
  public function createSummary(array $definitions): string
  {
    $listData = [];
    $aggrData = [];

    //    date_default_timezone_set('Europe/Zurich');
    $startDate = new \DateTimeImmutable('yesterday');
    $endDate = new \DateTimeImmutable('tomorrow');

    foreach ($definitions as $definition) {
      switch ($definition->summaryType) {
        case DailySummaryType::LIST:
          $logs = $this->svcLogRep->getDailyLogDataList(
            $startDate,
            $endDate,
            $definition->sourceID,
            $definition->sourceType,
            $definition->logLevel,
            $definition->logLevelCompare,
          );

          foreach ($logs as $log) {
            $log->setSourceTypeText($this->dataProvider->getSourceTypeText($log->getSourceType()));
            $log->setSourceIDText($this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType()));
          }

          $listData[] = new SummaryList($definition->title, $logs);
          break;

        case DailySummaryType::AGGR_LOG_LEVEL:
          $data = $this->svcLogRep->getDailyAggrLogLevel(
            $startDate,
            $endDate,
            $definition->logLevel,
            $definition->logLevelCompare,
          );

          foreach ($data as $key => $line) {
            if (array_key_exists($line['logLevel'], EventLog::ARR_LEVEL_TEXT)) {
              $data[$key]['logLevelText'] = EventLog::ARR_LEVEL_TEXT[$line['logLevel']];
            } else {
              // return '? (' . strval($this->logLevel) . ')';
            }
          }
          $aggrData[] = ['title' => $definition->title, 'data' => $data];

          break;
      }
    }

    $result = $this->twig->render('@SvcLog/daily_summary/index.html.twig', [
      'daily_lists' => $listData,
      'daily_aggrs' => $aggrData,
    ]);

    return $result;
  }
}
