<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DeleteAllLogsForbidden extends \Exception implements LogExceptionInterface
{
  protected $message = 'You cannot delete all logs, you need a filter.';

  public function getReason(): string
  {
    return $this->message;
  }
}
