<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryDefinitionNotImplement extends \Exception implements LogExceptionInterface
{
  /**
   * @var string
   */
  protected $message = 'The definition class for the daily summary does not implement DailySummaryDefinitionInterface.';

  public function getReason(): string
  {
    return $this->message;
  }
}
