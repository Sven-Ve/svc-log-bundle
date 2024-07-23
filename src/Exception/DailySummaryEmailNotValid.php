<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryEmailNotValid extends \Exception implements LogExceptionInterface
{
  /**
   * @var string
   */
  protected $message = 'The destination email is not a valid email address.';

  public function getReason(): string
  {
    return $this->message;
  }
}
