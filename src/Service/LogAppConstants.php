<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    final public const LOG_TYPE_APP_ERROR = 90003;

    public static function getSourceTypeText(int $sourceType): string
    {
        switch ($sourceType) {
            case self::LOG_TYPE_KERNEL_EXCEPTION:
                return 'kernel exception';
            case self::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION:
                return 'critical kernel exception';
            case self::LOG_TYPE_HACKING_ATTEMPT:
                return 'hacking attempt';
            case self::LOG_TYPE_APP_ERROR:
                return 'internal app error';
            default:
                return (string) $sourceType;
        }
    }
}
