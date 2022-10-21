<?php

namespace Svc\LogBundle\Service;

use DateInterval;
use DateTime;
use Svc\LogBundle\Repository\SvcLogRepository;

/**
 * Helper class for purging log records.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class PurgeHelper
{
  public function __construct(private readonly SvcLogRepository $svcLogRep)
  {
  }

  /**
   * aggregate data for monthly statistics.
   */
  public function purgeLogs(int $purgeMonth, bool $dryRun): int
  {
    $today = new DateTime();
    $firstDay = new DateTime($today->format('Y-m-01'));

    $subMonth = new DateInterval('P' . (string) $purgeMonth . 'M');

    $firstDayToKeep = $firstDay->sub($subMonth);

    return $this->svcLogRep->purgeOldData($firstDayToKeep, $dryRun);
  }
}
