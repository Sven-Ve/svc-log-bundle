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
  public function testLogLevelError(): void
  {
    $svcLog = new SvcLog();
    $svcLog->setLogLevel(EventLog::LEVEL_ERROR);

    $this->assertSame(EventLog::LEVEL_ERROR, $svcLog->getLogLevel());
    $this->assertSame('danger', $svcLog->getLogLevelBGColor());
    $this->assertSame('white', $svcLog->getLogLevelFGColor());
    $this->assertSame('bg-danger text-white', $svcLog->getLogLevelBootstrap5Class());
  }

  public function testLogLevelWarn(): void
  {
    $svcLog = new SvcLog();
    $svcLog->setLogLevel(EventLog::LEVEL_WARN);

    $this->assertSame(EventLog::LEVEL_WARN, $svcLog->getLogLevel(), 'testing results for logLevel WARN');
    $this->assertSame('warning', $svcLog->getLogLevelBGColor(), 'testing results for logLevel WARN');
    $this->assertSame('dark', $svcLog->getLogLevelFGColor(), 'testing results for logLevel WARN');
    $this->assertSame('bg-warning text-dark', $svcLog->getLogLevelBootstrap5Class(), 'testing results for logLevel WARN');
  }

  public function testLogLevelData(): void
  {
    $svcLog = new SvcLog();
    $svcLog->setLogLevel(EventLog::LEVEL_DATA);

    $this->assertSame(EventLog::LEVEL_DATA, $svcLog->getLogLevel(), 'testing results for logLevel DATA');
    $this->assertSame('success', $svcLog->getLogLevelBGColor(), 'testing results for logLevel DATA');
    $this->assertSame('white', $svcLog->getLogLevelFGColor(), 'testing results for logLevel DATA');
    $this->assertSame('bg-success text-white', $svcLog->getLogLevelBootstrap5Class(), 'testing results for logLevel DATA');
  }

  public function testLogDataSetCorrect(): void
  {
    $svcLog = new SvcLog();
    $this->assertGreaterThan($svcLog->getLogDate(), new \DateTime(), 'LogDate has to be equal or greater than now');

    $currDate = new \DateTime();
    $svcLog->setLogDate($currDate);
    $this->assertEquals($svcLog->getLogDate(), $currDate, 'LogDate has to be now');
  }
}
