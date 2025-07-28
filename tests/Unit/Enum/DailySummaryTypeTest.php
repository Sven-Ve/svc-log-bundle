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
use Svc\LogBundle\Enum\DailySummaryType;

class DailySummaryTypeTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = DailySummaryType::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(DailySummaryType::LIST, $cases);
        $this->assertContains(DailySummaryType::AGGR_LOG_LEVEL, $cases);
        $this->assertContains(DailySummaryType::COUNT_SOURCE_TYPE, $cases);
    }

    public function testEnumNames(): void
    {
        $this->assertSame('LIST', DailySummaryType::LIST->name);
        $this->assertSame('AGGR_LOG_LEVEL', DailySummaryType::AGGR_LOG_LEVEL->name);
        $this->assertSame('COUNT_SOURCE_TYPE', DailySummaryType::COUNT_SOURCE_TYPE->name);
    }

    public function testEnumInstanceEquality(): void
    {
        $allCases = DailySummaryType::cases();

        // Test that same enum instances are equal
        foreach ($allCases as $case) {
            $this->assertSame($case, $case);
        }

        // Test that different enum cases are not equal
        $this->assertNotSame(DailySummaryType::LIST, DailySummaryType::AGGR_LOG_LEVEL);
        $this->assertNotSame(DailySummaryType::AGGR_LOG_LEVEL, DailySummaryType::COUNT_SOURCE_TYPE);
        $this->assertNotSame(DailySummaryType::LIST, DailySummaryType::COUNT_SOURCE_TYPE);
    }

    public function testEnumInstanceComparison(): void
    {
        $list1 = DailySummaryType::LIST;
        $list2 = DailySummaryType::LIST;
        $aggr = DailySummaryType::AGGR_LOG_LEVEL;

        $this->assertSame($list1, $list2);
        $this->assertNotSame($list1, $aggr);
    }
}
