<?php

namespace Svc\LogBundle\DataProvider;

class DummyDataProvider implements DataProviderInterface
{


  public function getSourceIDText(int $id): string
  {
    return $id;
  }

  public function getSourceTypeText(int $id): string
  {
    return $id;
  }

  public function getSourceIDTextsArray(): array
  {
    return [];
  }

  public function getSourceTypeTextsArray(): array
  {
    return [];
  }
}
