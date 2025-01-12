<?php

namespace Svc\LogBundle\Service;

/**
 * internal used constants for for the svc-log bundle.
 */
class LogAppConstants
{
  /**
   * from here internal used.
   */
  final public const LOG_TYPE_INTERNAL_MIN = 90000;
  final public const LOG_TYPE_KERNEL_EXCEPTION = 90000;
  final public const LOG_TYPE_CRITICAL_KERNEL_EXCEPTION = 90001;
  final public const LOG_TYPE_HACKING_ATTEMPT = 90002;

  public static function getSourceTypeText(int $sourceType): string
  {
    switch ($sourceType) {
      case self::LOG_TYPE_KERNEL_EXCEPTION:
        return 'kernel exception';
      case self::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION:
        return 'critical kernel exception';
      case self::LOG_TYPE_HACKING_ATTEMPT:
        return 'hacking attempt';
      default:
        return (string) $sourceType;
    }
  }
}
