<?php

namespace Svc\LogBundle\Service;

use DateTime;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Svc\ParamBundle\Repository\ParamsRepository;

/**
 * Helper class for creating statistics
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class StatsHelper
{
  private $statMonRep;
  private $paramsRep;

  public function __construct(SvcLogStatMonthlyRepository $statMonRep, ParamsRepository $paramsRep)
  {
    $this->statMonRep = $statMonRep;
    $this->paramsRep = $paramsRep;
  }

    /**
   * aggragate data for monthly statistics
   *
   * @param boolean $fresh should the data reloaded completly (truncate table before)
   * @return void
   */
  public function aggrMonthly(bool $fresh = false)
  {
    $paramName = "svcLog_lastRunAggrMonthly";

    if ($fresh) {
      $lastRun = null;
    } else {
      $lastRun = $this->paramsRep->getDateTime($paramName);
    }
    $firstDay = $lastRun ? new DateTime($lastRun->format('Y-m-01')) : null;

    if ($fresh) {
      $this->statMonRep->truncateStatMonthlyTable();
      $deleted = 0;
    } else {
      $deleted = $this->statMonRep->deleteCurrentData($firstDay);
    }

    $inserted = $this->statMonRep->aggrData($firstDay);

    $this->paramsRep->setDateTime($paramName, new DateTime(), 'last aggregate refresh');

    return ["deleted" => $deleted, "inserted" => $inserted];
  }

}