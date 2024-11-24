<?php

namespace Svc\LogBundle\Service;

/**
 * internal used constants for for the svc-log bundle.
 */
class AppConstants
{
  final public const LOG_TYPE_KERNEL_EXCEPTION = 90000;
  final public const LOG_TYPE_CRITICAL_KERNEL_EXCEPTION = 90001;

  public static function getSourceTypeText(int $sourceType): string
  {
    switch ($sourceType) {
      case self::LOG_TYPE_KERNEL_EXCEPTION:
        return 'kernel exception';
      case self::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION:
        return 'critical kernel exception';
      default:
        return '';
    }
  }
}
