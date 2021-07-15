<?php

namespace Svc\LogBundle\Service;

/**
 * Helper class to log events
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class Logger
{

  public function log(string $message, int $sourceID, ?int $sourceType = 0)
  {
    dd("Hallo");
  }
}
