<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\EventListener;

use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Service\LogAppConstants;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener(event: 'kernel.exception')]
final class HttpExceptionListener
{
    public function __construct(
        private bool $enableLogger,
        private LogLevel $logLevelDefault,
        private LogLevel $logLevelCritical,
        private int $extraSleepTime,
        private EventLog $eventLog,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->enableLogger) {
            return;
        }

        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $requestUri = $request->getRequestUri();

        if ($exception instanceof HttpExceptionInterface) {
            $logType = LogAppConstants::LOG_TYPE_KERNEL_EXCEPTION;
            $statuscode = $exception->getStatusCode();
            $message = $exception->getMessage();
            if ($statuscode == 404 and str_starts_with($requestUri, '/sitemap')) {
                $level = LogLevel::WARN;
                $errorText = 'HTTP warning ' . $statuscode;
            } else {
                $level = $this->logLevelDefault;
                $errorText = 'HTTP error ' . $statuscode;
            }

            if ($this->extraSleepTime && $statuscode == 404) {
                sleep($this->extraSleepTime);
            }
        } else {
            $logType = LogAppConstants::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION;
            $statuscode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $exception->getMessage();
            $level = $this->logLevelCritical;
            $errorText = 'Internal server error';
        }

        $this->eventLog->writeLog(
            $statuscode,
            $logType,
            $level,
            message: $message,
            errorText: $errorText,
            httpStatusCode: $statuscode
        );
    }
}
