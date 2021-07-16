<?php

namespace Svc\LogBundle\Exception;

/**
 * An exception that is thrown by SvcLogBundle.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
interface LogExceptionInterface extends \Throwable
{
  /**
   * Returns a safe string that describes why verification failed.
   */
  public function getReason(): string;
}
