<?php

namespace Svc\LogBundle\Service;

use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Repository\SvcLogRepository;
use Twig\Environment;

class SummaryList {
  public function __construct(public string $title, public array $logItems)
  {
    
  }

}

class DailySummaryHelper{
  public function __construct(
    private readonly DataProviderInterface $dataProvider,
    private readonly Environment $twig,
    private readonly SvcLogRepository $svcLogRep)
  {
    
  }

/**
 * @param DailySumDef[] $definition
 */
  public function createSummary(array $definitions): string {

    $listData=[];

    foreach ($definitions as $definition) {
      $logs = $this->svcLogRep->getDailyLogDataList(
        $definition->sourceID,
        $definition->sourceType,
        $definition->logLevel,
        $definition->logLevelCompare,
      );

      foreach ($logs as $log) {
        $log->setSourceTypeText($this->dataProvider->getSourceTypeText($log->getSourceType()));
        $log->setSourceIDText($this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType()));
      }

      $listData[] = new SummaryList($definition->title,$logs);

  
    }

    

    $result = $this->twig->render("@SvcLog/daily_summary/index.html.twig", [
      "daily_lists" => $listData
    ]);
    
    return $result;
  }
}