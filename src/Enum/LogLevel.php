<?php

namespace Svc\LogBundle\Enum;

enum LogLevel: int
{
  case ALL = 0;
  case DEBUG = 1;
  case INFO = 2;
  /*
   * data is a special log level to store access data (page views, ...).
   */
  case DATA = 3;
  case WARN = 4;
  case ERROR = 5;
  case FATAL = 6;

  public function label(): string
  {
    return match ($this) {
      LogLevel::ALL => 'all',
      LogLevel::DEBUG => 'debug',
      LogLevel::INFO => 'info',
      LogLevel::DATA => 'data',
      LogLevel::WARN => 'warn',
      LogLevel::ERROR => 'error',
      LogLevel::FATAL => 'fatal',
    };
  }
}
