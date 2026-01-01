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

namespace Svc\LogBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Enum\ComparisonOperator;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Enum\LogLevel;

/**
 * Testing the DailySumDef entity class.
 */
final class DailySumDefTest extends TestCase
{
    public function testConstructorSetsRequiredProperties(): void
    {
        $title = 'Test Summary';
        $summaryType = DailySummaryType::LIST;

        $dailySumDef = new DailySumDef($title, $summaryType);

        $this->assertSame($title, $dailySumDef->title);
        $this->assertSame($summaryType, $dailySumDef->summaryType);
    }

    public function testDefaultValues(): void
    {
        $dailySumDef = new DailySumDef('Test', DailySummaryType::LIST);

        $this->assertNull($dailySumDef->sourceID);
        $this->assertNull($dailySumDef->sourceType);
        $this->assertNull($dailySumDef->logLevel);
        $this->assertSame(ComparisonOperator::EQUAL, $dailySumDef->logLevelCompare);
        $this->assertFalse($dailySumDef->hideWhenZero);
        $this->assertFalse($dailySumDef->hideWhenEmpty);
        $this->assertSame(null, $dailySumDef->countSourceTypeDef ?? null);
    }

    public function testOptionalPropertiesCanBeSet(): void
    {
        $dailySumDef = new DailySumDef('Test', DailySummaryType::LIST);

        $dailySumDef->sourceID = 123;
        $dailySumDef->sourceType = 456;
        $dailySumDef->logLevel = LogLevel::ERROR;
        $dailySumDef->logLevelCompare = ComparisonOperator::GREATER_THAN;
        $dailySumDef->hideWhenZero = true;
        $dailySumDef->hideWhenEmpty = true;
        $dailySumDef->countSourceTypeDef = ['key' => 'value'];

        $this->assertSame(123, $dailySumDef->sourceID);
        $this->assertSame(456, $dailySumDef->sourceType);
        $this->assertSame(LogLevel::ERROR, $dailySumDef->logLevel);
        $this->assertSame(ComparisonOperator::GREATER_THAN, $dailySumDef->logLevelCompare);
        $this->assertTrue($dailySumDef->hideWhenZero);
        $this->assertTrue($dailySumDef->hideWhenEmpty);
        $this->assertSame(['key' => 'value'], $dailySumDef->countSourceTypeDef);
    }

    public function testWithAllSummaryTypes(): void
    {
        foreach (DailySummaryType::cases() as $type) {
            $dailySumDef = new DailySumDef('Test ' . $type->name, $type);
            $this->assertSame($type, $dailySumDef->summaryType);
        }
    }

    public function testWithAllLogLevels(): void
    {
        $dailySumDef = new DailySumDef('Test', DailySummaryType::LIST);

        foreach (LogLevel::cases() as $logLevel) {
            $dailySumDef->logLevel = $logLevel;
            $this->assertSame($logLevel, $dailySumDef->logLevel);
        }
    }

    public function testWithAllComparisonOperators(): void
    {
        $dailySumDef = new DailySumDef('Test', DailySummaryType::AGGR_LOG_LEVEL);

        foreach (ComparisonOperator::cases() as $operator) {
            $dailySumDef->logLevelCompare = $operator;
            $this->assertSame($operator, $dailySumDef->logLevelCompare);
        }
    }
}
