<?php

namespace Svc\LogBundle\Enum;

enum LogLevel: int
{
  case DEBUG = 1;
  case INFO = 2;
  /*
   * data is a special log level to store access data (page views, ...).
   */
  case DATA = 3;
  case WARN = 4;
  case ERROR = 5;
  case CRITICAL = 6;
  case ALERT = 7;
  case EMERGENCY = 8;

  public function label(): string
  {
    return static::getLabel($this);
  }

  public static function getLabel(self $value): string
  {
    return match ($value) {
      LogLevel::DEBUG => 'debug',
      LogLevel::INFO => 'info',
      LogLevel::DATA => 'data',
      LogLevel::WARN => 'warn',
      LogLevel::ERROR => 'error',
      LogLevel::CRITICAL => 'critical',
      LogLevel::ALERT => 'alert',
      LogLevel::EMERGENCY => 'emergency',
    };
  }

  public static function getLogLevelfromInt(?int $logLevelInt, ?LogLevel $defaultLogLevel = null): ?LogLevel
  {
    $logLevel = self::tryFrom($logLevelInt);

    return $logLevel ?? $defaultLogLevel;
  }
}
