<?php

declare(strict_types=1);

namespace Svc\LogBundle\Tests\Unit\DataProvider;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\DataProvider\GeneralDataProvider;
use Svc\LogBundle\Entity\SvcLogStatMonthly;

/**
 * testing the SvcLogStatMonthly entity class.
 */
final class GeneralDataProviderTest extends TestCase
{
  public function testGetSourceType(): void
  {
    $dataProvider = new GeneralDataProvider();
    $this->assertSame($dataProvider->getSourceTypeText(10), '10');
  }

  public function testGetSourceId(): void
  {
    $dataProvider = new GeneralDataProvider();
    $this->assertSame($dataProvider->getSourceIDText(10), '10');
  }

  public function testGetSourceIdTextArray(): void
  {
    $dataProvider = new GeneralDataProvider();
    $this->assertSame($dataProvider->getSourceIDTextsArray(), []);
  }

  public function testGetSourceTypeTextArray(): void
  {
    $dataProvider = new GeneralDataProvider();
    $this->assertSame($dataProvider->getSourceTypeTextsArray(), []);
  }
}
