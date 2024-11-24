<?php

namespace Svc\LogBundle\EventListener;

use Svc\LogBundle\Service\AppConstants;
use Svc\LogBundle\Service\EventLog;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener(event: 'kernel.exception')]
class HttpExceptionListener
{
  public function __construct(
    private bool $enableLogger,
    private int $logLevelDefault,
    private int $logLevelCritical,
    private EventLog $eventLog)
  {
  }

  public function onKernelException(ExceptionEvent $event): void
  {
    if (!$this->enableLogger) {
      return;
    }

    // You get the exception object from the received event
    $exception = $event->getThrowable();

    if ($exception instanceof HttpExceptionInterface) {
      $logType = AppConstants::LOG_TYPE_KERNEL_EXCEPTION;
      $statuscode = $exception->getStatusCode();
      $message = $exception->getMessage();
      $level = $this->logLevelDefault;
      $errorText = 'HTTP error ' . $statuscode;
    } else {
      $logType = AppConstants::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION;
      $statuscode = Response::HTTP_INTERNAL_SERVER_ERROR;
      $message = $exception->getMessage();
      $level = $this->logLevelCritical;
      $errorText = 'Internal server error';
    }

    $this->eventLog->log($statuscode, $logType, ['message' => $message, 'level' => $level, 'errorText' => $errorText]);
  }
}
