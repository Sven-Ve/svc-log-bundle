<?php

namespace Svc\LogBundle\Service;

use Psr\Log\LoggerInterface;
use Svc\LogBundle\Entity\SvcLog;

class LoggerHelper
{
  public function __construct(
    private readonly LoggerInterface $svclogLogger,
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
      default => 'info',
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
    if ($log->getIp()) {
      $extraData['ip'] = $log->getIp();
    }
    if ($log->getPlatform()) {
      $extraData['platform'] = $log->getPlatform();
    }
    if ($log->getReferer()) {
      $extraData['referer'] = $log->getReferer();
    }
    if ($log->getBrowser()) {
      $extraData['browser'] = $log->getBrowser() . ' ' . $log->getBrowserVersion();
    }
    if ($log->getOs()) {
      $extraData['os'] = $log->getOs() . ' ' . $log->getOsVersion();
    }
    if ($log->getErrorText()) {
      $extraData['errorText'] = $log->getErrorText();
    }

    try {
      $this->svclogLogger->$logLevel($log->getMessage() ?? $log->getLogLevelText(), $extraData);
    } catch (\Exception) {
      return false;
    }

    return true;
  }
}
