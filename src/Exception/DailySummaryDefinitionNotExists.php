<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryDefinitionNotExists extends \Exception implements LogExceptionInterface
{
  /**
   * @var string
   */
  protected $message = 'The definition class for the daily summary report does not exists.';

  public function getReason(): string
  {
    return $this->message;
  }
}
