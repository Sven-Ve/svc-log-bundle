<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Service\LogAppConstants;

class LogAppConstantsTest extends TestCase
{
    public function testConstants(): void
    {
        $this->assertSame(90000, LogAppConstants::LOG_TYPE_INTERNAL_MIN);
        $this->assertSame(90000, LogAppConstants::LOG_TYPE_KERNEL_EXCEPTION);
        $this->assertSame(90001, LogAppConstants::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION);
        $this->assertSame(90002, LogAppConstants::LOG_TYPE_HACKING_ATTEMPT);
        $this->assertSame(90003, LogAppConstants::LOG_TYPE_APP_ERROR);
    }

    public function testGetSourceTypeTextWithKnownTypes(): void
    {
        $this->assertSame(
            'kernel exception',
            LogAppConstants::getSourceTypeText(LogAppConstants::LOG_TYPE_KERNEL_EXCEPTION)
        );

        $this->assertSame(
            'critical kernel exception',
            LogAppConstants::getSourceTypeText(LogAppConstants::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION)
        );

        $this->assertSame(
            'hacking attempt',
            LogAppConstants::getSourceTypeText(LogAppConstants::LOG_TYPE_HACKING_ATTEMPT)
        );

        $this->assertSame(
            'internal app error',
            LogAppConstants::getSourceTypeText(LogAppConstants::LOG_TYPE_APP_ERROR)
        );
    }

    public function testGetSourceTypeTextWithUnknownType(): void
    {
        $unknownType = 12345;
        $this->assertSame('12345', LogAppConstants::getSourceTypeText($unknownType));

        $anotherUnknownType = 0;
        $this->assertSame('0', LogAppConstants::getSourceTypeText($anotherUnknownType));

        $negativeType = -1;
        $this->assertSame('-1', LogAppConstants::getSourceTypeText($negativeType));
    }

    public function testAllConstantsAreInInternalRange(): void
    {
        $this->assertGreaterThanOrEqual(
            LogAppConstants::LOG_TYPE_INTERNAL_MIN,
            LogAppConstants::LOG_TYPE_KERNEL_EXCEPTION
        );

        $this->assertGreaterThanOrEqual(
            LogAppConstants::LOG_TYPE_INTERNAL_MIN,
            LogAppConstants::LOG_TYPE_CRITICAL_KERNEL_EXCEPTION
        );

        $this->assertGreaterThanOrEqual(
            LogAppConstants::LOG_TYPE_INTERNAL_MIN,
            LogAppConstants::LOG_TYPE_HACKING_ATTEMPT
        );

        $this->assertGreaterThanOrEqual(
            LogAppConstants::LOG_TYPE_INTERNAL_MIN,
            LogAppConstants::LOG_TYPE_APP_ERROR
        );
    }
}
