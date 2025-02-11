<?php

namespace Svc\LogBundle\EventListener;

use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Service\LogAppConstants;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener(event: 'kernel.exception')]
class HttpExceptionListener
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

    if ($exception instanceof HttpExceptionInterface) {
      $logType = LogAppConstants::LOG_TYPE_KERNEL_EXCEPTION;
      $statuscode = $exception->getStatusCode();
      $message = $exception->getMessage();
      $level = $this->logLevelDefault;
      $errorText = 'HTTP error ' . $statuscode;

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
