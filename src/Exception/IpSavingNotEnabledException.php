<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class IpSavingNotEnabledException extends \Exception implements LogExceptionInterface
{
  /**
   * @var string
   */
  protected $message = 'IP saving is disabled. This function is not allowed.';

  public function getReason(): string
  {
    return $this->message;
  }
}
