<?php

namespace Svc\LogBundle\Service;


/**
 * Helper class for displaing statistics
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class LogStatistics
{

  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array {
    $data = [];
    $data['a'] = 'wir haben was';
    return $data;
  }
}
