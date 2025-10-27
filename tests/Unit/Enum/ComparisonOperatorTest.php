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

namespace Svc\LogBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Enum\ComparisonOperator;

class ComparisonOperatorTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('=', ComparisonOperator::EQUAL->value);
        $this->assertSame('>', ComparisonOperator::GREATER_THAN->value);
        $this->assertSame('>=', ComparisonOperator::GREATER_THAN_OR_EQUAL->value);
        $this->assertSame('<', ComparisonOperator::LESS_THAN->value);
        $this->assertSame('<=', ComparisonOperator::LESS_THAN_OR_EQUA->value);
        $this->assertSame('!=', ComparisonOperator::NOT_EQUAL->value);
    }

    public function testFromMethod(): void
    {
        $this->assertSame(ComparisonOperator::EQUAL, ComparisonOperator::from('='));
        $this->assertSame(ComparisonOperator::GREATER_THAN, ComparisonOperator::from('>'));
        $this->assertSame(ComparisonOperator::GREATER_THAN_OR_EQUAL, ComparisonOperator::from('>='));
        $this->assertSame(ComparisonOperator::LESS_THAN, ComparisonOperator::from('<'));
        $this->assertSame(ComparisonOperator::LESS_THAN_OR_EQUA, ComparisonOperator::from('<='));
        $this->assertSame(ComparisonOperator::NOT_EQUAL, ComparisonOperator::from('!='));
    }

    public function testTryFromMethodWithValidValues(): void
    {
        $this->assertSame(ComparisonOperator::EQUAL, ComparisonOperator::tryFrom('='));
        $this->assertSame(ComparisonOperator::GREATER_THAN, ComparisonOperator::tryFrom('>'));
        $this->assertSame(ComparisonOperator::GREATER_THAN_OR_EQUAL, ComparisonOperator::tryFrom('>='));
        $this->assertSame(ComparisonOperator::LESS_THAN, ComparisonOperator::tryFrom('<'));
        $this->assertSame(ComparisonOperator::LESS_THAN_OR_EQUA, ComparisonOperator::tryFrom('<='));
        $this->assertSame(ComparisonOperator::NOT_EQUAL, ComparisonOperator::tryFrom('!='));
    }

    public function testTryFromBehaviorWithInvalidAndValidValues(): void
    {
        // Test both valid and invalid values in one test
        $testCases = [
            '=' => ComparisonOperator::EQUAL,
            '>' => ComparisonOperator::GREATER_THAN,
            'invalid' => null,
            '' => null,
            '==' => null,
        ];

        foreach ($testCases as $input => $expected) {
            $result = ComparisonOperator::tryFrom($input);
            $this->assertEquals($expected, $result, "Failed for input: '$input'");
        }
    }

    public function testCasesMethod(): void
    {
        $cases = ComparisonOperator::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(ComparisonOperator::EQUAL, $cases);
        $this->assertContains(ComparisonOperator::GREATER_THAN, $cases);
        $this->assertContains(ComparisonOperator::GREATER_THAN_OR_EQUAL, $cases);
        $this->assertContains(ComparisonOperator::LESS_THAN, $cases);
        $this->assertContains(ComparisonOperator::LESS_THAN_OR_EQUA, $cases);
        $this->assertContains(ComparisonOperator::NOT_EQUAL, $cases);
    }

    public function testFromMethodThrowsOnInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        ComparisonOperator::from('invalid');
    }
}
