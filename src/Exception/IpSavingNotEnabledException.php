<?php

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class IpSavingNotEnabledException extends \Exception implements LogExceptionInterface
{
  public function getReason(): string
  {
    return 'IP saving is disabled. This function is not allowed.';
  }
}
