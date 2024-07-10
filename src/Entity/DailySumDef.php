<?php

namespace Svc\LogBundle\Entity;

use Svc\LogBundle\Enum\ComparisonOperator;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Enum\LogLevel;

class DailySumDef
{
  public function __construct(public string $title, public DailySummaryType $summaryType)
  {
  }

  public ?int $sourceID = null;

  public ?int $sourceType = null;

  public ?LogLevel $logLevel = null;

  public ?ComparisonOperator $logLevelCompare = ComparisonOperator::EQUAL;
}