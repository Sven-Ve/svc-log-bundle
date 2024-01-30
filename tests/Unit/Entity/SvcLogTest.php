<?php

declare(strict_types=1);

namespace Svc\LogBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Service\EventLog;

/**
 * testing the SvcLog entity class.
 */
final class SvcLogTest extends TestCase
{
  /**
   * @dataProvider logLevelDataProvider
   */
  public function testLogLevelSetCorrectAttributes(int $logLevel, string $bgColor, string $fgColor, string $name): void
  {
    $svcLog = new SvcLog();
    $svcLog->setLogLevel($logLevel);

    $this->assertSame($logLevel, $svcLog->getLogLevel(), 'Testing logLevel ' . $name);
    $this->assertSame($bgColor, $svcLog->getLogLevelBGColor(), 'Testing logLevel ' . $name);
    $this->assertSame($fgColor, $svcLog->getLogLevelFGColor(), 'Testing logLevel ' . $name);
    $this->assertSame('bg-' . $bgColor . ' text-' . $fgColor, $svcLog->getLogLevelBootstrap5Class(), 'Testing logLevel ' . $name);
    $this->assertSame($name, $svcLog->getLogLevelText(), 'Testing logLevel ' . $name);
  }

  public static function logLevelDataProvider(): \Generator
  {
    yield 'logLevel error' => [EventLog::LEVEL_ERROR, 'danger', 'white', 'error'];
    yield 'logLevel warn' => [EventLog::LEVEL_WARN, 'warning', 'dark', 'warn'];
    yield 'logLevel data' => [EventLog::LEVEL_DATA, 'success', 'white', 'data'];
    yield 'logLevel info' => [EventLog::LEVEL_INFO, 'primary', 'white', 'info'];
    yield 'logLevel debug' => [EventLog::LEVEL_DEBUG, 'secondary', 'white', 'debug'];
    yield 'logLevel fatal' => [EventLog::LEVEL_FATAL, 'danger', 'white', 'fatal'];
    yield 'logLevel alert' => [EventLog::LEVEL_ALERT, 'danger', 'white', 'alert'];
    yield 'logLevel emergency' => [EventLog::LEVEL_EMERGENCY, 'danger', 'white', 'emergency'];
    yield 'logLevel 1000' => [1000, 'secondary', 'white', '? (1000)'];
  }

  public function testLogDateSetCorrect(): void
  {
    $svcLog = new SvcLog();
    $this->assertGreaterThanOrEqual($svcLog->getLogDate(), new \DateTime(), 'LogDate has to be equal or greater than now');

    $currDate = new \DateTime();
    $svcLog->setLogDate($currDate);
    $this->assertEquals($svcLog->getLogDate(), $currDate, 'LogDate has to be now');
  }
}
