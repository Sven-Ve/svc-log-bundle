<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryEmailNotDefined extends \Exception implements LogExceptionInterface
{
  /**
   * @var string
   */
  protected $message = 'The destination email for the daily summary is not defined.';

  public function getReason(): string
  {
    return $this->message;
  }
}
