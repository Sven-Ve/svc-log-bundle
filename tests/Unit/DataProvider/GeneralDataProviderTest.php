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

namespace Svc\LogBundle\Tests\Unit\DataProvider;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\DataProvider\GeneralDataProvider;

/**
 * testing the SvcLogStatMonthly entity class.
 */
final class GeneralDataProviderTest extends TestCase
{
    public function testGetSourceType(): void
    {
        $dataProvider = new GeneralDataProvider();
        $this->assertSame($dataProvider->getSourceTypeText(10), '10');
    }

    public function testGetSourceId(): void
    {
        $dataProvider = new GeneralDataProvider();
        $this->assertSame($dataProvider->getSourceIDText(10), '10');
    }

    public function testGetSourceIdTextArray(): void
    {
        $dataProvider = new GeneralDataProvider();
        $this->assertSame($dataProvider->getSourceIDTextsArray(), []);
    }

    public function testGetSourceTypeTextArray(): void
    {
        $dataProvider = new GeneralDataProvider();
        $this->assertSame($dataProvider->getSourceTypeTextsArray(), []);
    }
}
