<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            LogLevel::WARN => 'warning',
            LogLevel::ERROR => 'error',
            LogLevel::CRITICAL => 'critical',
            LogLevel::ALERT => 'alert',
            LogLevel::EMERGENCY => 'emergency',
        };
    }

    public static function getLogLevelfromInt(?int $logLevelInt, ?LogLevel $defaultLogLevel = null): ?LogLevel
    {
        if (!$logLevelInt) {
            return $defaultLogLevel;
        }

        $logLevel = self::tryFrom($logLevelInt);

        return $logLevel ?? $defaultLogLevel;
    }
}
