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

namespace Svc\LogBundle\Entity;

use Svc\LogBundle\Enum\ComparisonOperator;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Enum\LogLevel;

class DailySumDef
{
    public function __construct(public string $title, public DailySummaryType $summaryType)
    {
    }

    public ?int $sourceID = null;

    public ?int $sourceType = null;

    public ?LogLevel $logLevel = null;

    public ?ComparisonOperator $logLevelCompare = ComparisonOperator::EQUAL;

    /**
     * hide lines with result = 0.
     */
    public bool $hideWhenZero = false;

    /**
     * hide section when all results are zero.
     */
    public bool $hideWhenEmpty = false;

    /**
     * @var array<mixed>
     */
    public ?array $countSourceTypeDef;
}
