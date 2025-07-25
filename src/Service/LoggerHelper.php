<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Service;

use Psr\Log\LoggerInterface;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\LogLevel;

/**
 * Helper to send logs to the svc-log logger (monolog).
 *
 * @author Sven Vetter <<dev@sv-systems.com>
 */
class LoggerHelper
{
    public function __construct(
        private readonly LoggerInterface $svclogLogger,
    ) {
    }

    public function send(SvcLog $log): bool
    {
        $logLevel = match ($log->getLogLevel()) {
            LogLevel::DEBUG => 'debug',
            LogLevel::INFO => 'info',
            LogLevel::WARN => 'warning',
            LogLevel::ERROR => 'error',
            LogLevel::CRITICAL => 'critical',
            LogLevel::ALERT => 'alert',
            LogLevel::EMERGENCY => 'emergency',
            default => 'info',
        };

        $extraData = [
            'sender' => 'svc_log',
            'log_level' => $log->getLogLevel()->value . ' ' . $log->getLogLevelText(),
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
