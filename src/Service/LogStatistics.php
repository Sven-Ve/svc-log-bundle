<?php

namespace Svc\LogBundle\Service;

use Svc\LogBundle\Repository\SvcLogRepository;

/**
 * Helper class for displaing statistics
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class LogStatistics
{

  private $svcLogRep;

  public function __construct(SvcLogRepository $svcLogRep)
  {
    $this->svcLogRep = $svcLogRep;
  }

  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array {
    $data = [];

    $logEntries = $this->svcLogRep->getLogPaginator(1,$sourceID, $sourceType);
    $data['records'] = $logEntries;
    return $data;
  }
}
