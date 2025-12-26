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

namespace Svc\LogBundle\Tests\Command;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Command\StatMonthlyCommand;
use Svc\LogBundle\Service\StatsHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowMockObjectsWithoutExpectations]
class StatMonthlyCommandTest extends TestCase
{
    private StatMonthlyCommand $command;

    private StatsHelper&MockObject $statsHelper;

    protected function setUp(): void
    {
        $this->statsHelper = $this->createMock(StatsHelper::class);
        $this->command = new StatMonthlyCommand($this->statsHelper);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(StatMonthlyCommand::class, $this->command);
    }

    public function testInvokeWithoutFresh(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Create monthly statistics');

        $io->expects($this->once())
            ->method('success')
            ->with('Aggragation successfully runs. 150 statistic records created.');

        $this->statsHelper->expects($this->once())
            ->method('aggrMonthly')
            ->with(false)
            ->willReturn(['inserted' => 150]);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeWithFresh(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Create monthly statistics');

        $io->expects($this->once())
            ->method('success')
            ->with('Aggragation successfully runs. 500 statistic records created.');

        $this->statsHelper->expects($this->once())
            ->method('aggrMonthly')
            ->with(true)
            ->willReturn(['inserted' => 500]);

        $result = $this->command->__invoke($io, fresh: true);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeWithZeroRecords(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Create monthly statistics');

        $io->expects($this->once())
            ->method('success')
            ->with('Aggragation successfully runs. 0 statistic records created.');

        $this->statsHelper->expects($this->once())
            ->method('aggrMonthly')
            ->with(false)
            ->willReturn(['inserted' => 0]);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::SUCCESS, $result);
    }
}
