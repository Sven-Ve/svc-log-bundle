<?php

namespace Svc\LogBundle\Service;

use Psr\Log\LoggerInterface;
use Svc\LogBundle\Entity\SvcLog;

class LoggerHelper
{
  public function __construct(
    private readonly LoggerInterface $svclogLogger
  ) {
  }

  public function send(SvcLog $log): bool
  {
    $logLevel = match ($log->getLogLevel()) {
      EventLog::LEVEL_DEBUG => 'debug',
      EventLog::LEVEL_INFO => 'info',
      EventLog::LEVEL_WARN => 'warning',
      EventLog::LEVEL_ERROR => 'error',
      EventLog::LEVEL_FATAL => 'critical',
      EventLog::LEVEL_ALERT => 'alert',
      EventLog::LEVEL_EMERGENCY => 'emergency',
      default => 'info'
    };

    $extraData = [
      'sender' => 'svc_log',
      'log_level' => $log->getLogLevel() . ' ' . $log->getLogLevelText(),
    ];
    if ($log->getUserName()) {
      $extraData['user'] = $log->getUserName();
    }
    if ($log->getSourceType()) {
      $extraData['source_type'] = $log->getSourceType();
    }
    if ($log->getSourceID()) {
      $extraData['source_id'] = $log->getSourceID();
    }

    $this->svclogLogger->$logLevel($log->getMessage(), $extraData);

    return true;
  }
}
