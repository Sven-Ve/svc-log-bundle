<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryCannotSendMail extends \Exception implements LogExceptionInterface
{
  /**
   * @var string
   */
  protected $message = 'Cannot send the daily summary email.';

  public function getReason(): string
  {
    return $this->message;
  }
}
