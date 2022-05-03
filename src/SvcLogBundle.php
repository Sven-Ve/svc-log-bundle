<?php

namespace Svc\LogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SvcLogBundle extends Bundle
{
  public function getPath(): string
  {
    return \dirname(__DIR__);
  }
}
