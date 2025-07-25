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

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Entity\SvcLogStatMonthly;
use Svc\LogBundle\Enum\LogLevel;

/**
 * testing the SvcLogStatMonthly entity class.
 */
final class SvcLogStatMonthlyTest extends TestCase
{
    public function testEntitySetAndGet(): void
    {
        $svcLogStat = new SvcLogStatMonthly();

        $svcLogStat->setSourceID(10);
        $this->assertSame($svcLogStat->getSourceID(), 10, 'Check SourceID');

        $svcLogStat->setSourceType(11);
        $this->assertSame($svcLogStat->getSourceType(), 11, 'Check SourceType');

        $svcLogStat->setLogLevel(LogLevel::ERROR);
        $this->assertSame($svcLogStat->getLogLevel(), LogLevel::ERROR, 'Check LogLevel');

        $svcLogStat->setLogCount(12);
        $this->assertSame($svcLogStat->getLogCount(), 12, 'Check LogCount');

        $svcLogStat->setLogCount(0);
        $this->assertSame($svcLogStat->getLogCount(), 0, 'Check LogCount (0)');

        $svcLogStat->setLogCount(-1);
        $this->assertSame($svcLogStat->getLogCount(), -1, 'Check LogCount (-1)');
    }
}
