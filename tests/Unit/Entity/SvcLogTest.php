<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Tests\Unit\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\LogLevel;

/**
 * testing the SvcLog entity class.
 */
final class SvcLogTest extends TestCase
{
    #[DataProvider('logLevelDataProvider')]
    public function testLogLevelSetCorrectAttributes(LogLevel $logLevel, string $bgColor, string $fgColor, string $name): void
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
        yield 'logLevel error' => [LogLevel::ERROR, 'danger', 'white', 'error'];
        yield 'logLevel warn' => [LogLevel::WARN, 'warning', 'dark', 'warning'];
        yield 'logLevel data' => [LogLevel::DATA, 'success', 'white', 'data'];
        yield 'logLevel info' => [LogLevel::INFO, 'primary', 'white', 'info'];
        yield 'logLevel debug' => [LogLevel::DEBUG, 'secondary', 'white', 'debug'];
        yield 'logLevel fatal' => [LogLevel::CRITICAL, 'danger', 'white', 'critical'];
        yield 'logLevel alert' => [LogLevel::ALERT, 'danger', 'white', 'alert'];
        yield 'logLevel emergency' => [LogLevel::EMERGENCY, 'danger', 'white', 'emergency'];
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
