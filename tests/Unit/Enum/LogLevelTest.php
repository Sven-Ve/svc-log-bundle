<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Enum\LogLevel;

class LogLevelTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame(1, LogLevel::DEBUG->value);
        $this->assertSame(2, LogLevel::INFO->value);
        $this->assertSame(3, LogLevel::DATA->value);
        $this->assertSame(4, LogLevel::WARN->value);
        $this->assertSame(5, LogLevel::ERROR->value);
        $this->assertSame(6, LogLevel::CRITICAL->value);
        $this->assertSame(7, LogLevel::ALERT->value);
        $this->assertSame(8, LogLevel::EMERGENCY->value);
    }

    public function testLabelMethod(): void
    {
        $this->assertSame('debug', LogLevel::DEBUG->label());
        $this->assertSame('info', LogLevel::INFO->label());
        $this->assertSame('data', LogLevel::DATA->label());
        $this->assertSame('warning', LogLevel::WARN->label());
        $this->assertSame('error', LogLevel::ERROR->label());
        $this->assertSame('critical', LogLevel::CRITICAL->label());
        $this->assertSame('alert', LogLevel::ALERT->label());
        $this->assertSame('emergency', LogLevel::EMERGENCY->label());
    }

    public function testGetLabelStaticMethod(): void
    {
        $this->assertSame('debug', LogLevel::getLabel(LogLevel::DEBUG));
        $this->assertSame('info', LogLevel::getLabel(LogLevel::INFO));
        $this->assertSame('data', LogLevel::getLabel(LogLevel::DATA));
        $this->assertSame('warning', LogLevel::getLabel(LogLevel::WARN));
        $this->assertSame('error', LogLevel::getLabel(LogLevel::ERROR));
        $this->assertSame('critical', LogLevel::getLabel(LogLevel::CRITICAL));
        $this->assertSame('alert', LogLevel::getLabel(LogLevel::ALERT));
        $this->assertSame('emergency', LogLevel::getLabel(LogLevel::EMERGENCY));
    }

    public function testGetLogLevelFromIntWithValidValues(): void
    {
        $this->assertSame(LogLevel::DEBUG, LogLevel::getLogLevelfromInt(1));
        $this->assertSame(LogLevel::INFO, LogLevel::getLogLevelfromInt(2));
        $this->assertSame(LogLevel::DATA, LogLevel::getLogLevelfromInt(3));
        $this->assertSame(LogLevel::WARN, LogLevel::getLogLevelfromInt(4));
        $this->assertSame(LogLevel::ERROR, LogLevel::getLogLevelfromInt(5));
        $this->assertSame(LogLevel::CRITICAL, LogLevel::getLogLevelfromInt(6));
        $this->assertSame(LogLevel::ALERT, LogLevel::getLogLevelfromInt(7));
        $this->assertSame(LogLevel::EMERGENCY, LogLevel::getLogLevelfromInt(8));
    }

    public function testGetLogLevelFromIntWithNullValue(): void
    {
        $this->assertNull(LogLevel::getLogLevelfromInt(null));
        $this->assertSame(LogLevel::INFO, LogLevel::getLogLevelfromInt(null, LogLevel::INFO));
    }

    public function testGetLogLevelFromIntWithZeroValue(): void
    {
        $this->assertNull(LogLevel::getLogLevelfromInt(0));
        $this->assertSame(LogLevel::ERROR, LogLevel::getLogLevelfromInt(0, LogLevel::ERROR));
    }

    public function testGetLogLevelFromIntWithInvalidValue(): void
    {
        $this->assertNull(LogLevel::getLogLevelfromInt(999));
        $this->assertSame(LogLevel::WARN, LogLevel::getLogLevelfromInt(999, LogLevel::WARN));
    }

    public function testGetLogLevelFromIntWithNegativeValue(): void
    {
        $this->assertNull(LogLevel::getLogLevelfromInt(-1));
        $this->assertSame(LogLevel::DEBUG, LogLevel::getLogLevelfromInt(-1, LogLevel::DEBUG));
    }
}
