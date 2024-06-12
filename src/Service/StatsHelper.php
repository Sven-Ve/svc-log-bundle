<?php

namespace Svc\LogBundle\Service;

use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;
use Svc\ParamBundle\Repository\ParamsRepository;

/**
 * Helper class for creating statistics.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class StatsHelper
{
  public function __construct(
    private readonly SvcLogStatMonthlyRepository $statMonRep,
    private readonly ParamsRepository $paramsRep
  ) {
  }

  /**
   * aggregate data for monthly statistics.
   *
   * @param bool $fresh should the data reloaded completely (truncate table before)
   */
  public function aggrMonthly(bool $fresh = false): array
  {
    $paramName = 'svcLog_lastRunAggrMonthly';

    if ($fresh) {
      $lastRun = null;
    } else {
      $lastRun = $this->paramsRep->getDateTime($paramName);
    }
    $firstDay = $lastRun ? new \DateTime($lastRun->format('Y-m-01')) : null;

    if ($fresh) {
      $this->statMonRep->truncateStatMonthlyTable();
      $deleted = 0;
    } else {
      $deleted = $this->statMonRep->deleteCurrentData($firstDay);
    }

    $inserted = $this->statMonRep->aggrData($firstDay);

    $this->paramsRep->setDateTime($paramName, new \DateTime(), 'last aggregate refresh', true);

    return ['deleted' => $deleted, 'inserted' => $inserted];
  }
}
