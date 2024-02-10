<?php

namespace Svc\LogBundle\Service;

use Sentry\Severity;
use Svc\LogBundle\Entity\SvcLog;

class SentryHelper
{
  public function send(SvcLog $log): bool
  {
    $logLevel = match ($log->getLogLevel()) {
      EventLog::LEVEL_WARN => Severity::warning(),
      EventLog::LEVEL_ERROR => Severity::error(),
      EventLog::LEVEL_FATAL => Severity::fatal(),
      EventLog::LEVEL_EMERGENCY => Severity::fatal(),
      EventLog::LEVEL_ALERT => Severity::fatal(),
      default => Severity::info()
    };

    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($log, $logLevel): void {
      $scope->setUser(['username' => $log->getUserName()]);
      $scope->setTag('svc_sender', 'svc_log');
      $scope->setTag('svc_source_type', $log->getSourceType());

      $scope->setContext('svc_log', [
        'source_id' => $log->getSourceID(),
        'source_type' => $log->getSourceType(),
        'log_level' => $log->getLogLevel() . ' ' . $log->getLogLevelText(),
        'message' => $log->getMessage(),
        'error_text' => $log->getErrorText(),
      ]);
      \Sentry\captureMessage($log->getMessage() ?? 'SourceID=' . $log->getSourceID() . ', SourceType=' . $log->getSourceType(), $logLevel);
    });

    return true;
  }
}
