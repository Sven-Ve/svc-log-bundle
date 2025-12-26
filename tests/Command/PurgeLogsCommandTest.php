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
use Svc\LogBundle\Command\PurgeLogsCommand;
use Svc\LogBundle\Service\PurgeHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowMockObjectsWithoutExpectations]
class PurgeLogsCommandTest extends TestCase
{
    private PurgeLogsCommand $command;

    private PurgeHelper&MockObject $purgeHelper;

    protected function setUp(): void
    {
        $this->purgeHelper = $this->createMock(PurgeHelper::class);
        $this->command = new PurgeLogsCommand($this->purgeHelper);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(PurgeLogsCommand::class, $this->command);
    }

    public function testInvokeWithDefaultParameters(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Purge old log events');

        $io->expects($this->exactly(3))
            ->method('writeln')
            ->with($this->logicalOr(
                'Keep Month:6',
                'Dry run: no',
                '100 log records purged.'
            ));

        $io->expects($this->once())
            ->method('success')
            ->with('Purge successfull.');

        $this->purgeHelper->expects($this->once())
            ->method('purgeLogs')
            ->with(6, false)
            ->willReturn(100);

        $result = ($this->command)($io);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeWithDryRun(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Purge old log events');

        $io->expects($this->exactly(3))
            ->method('writeln')
            ->with($this->logicalOr(
                'Keep Month:3',
                'Dry run: yes',
                '50 log records purged. (dryrun)'
            ));

        $io->expects($this->once())
            ->method('success')
            ->with('Purge successfull. (dryrun)');

        $this->purgeHelper->expects($this->once())
            ->method('purgeLogs')
            ->with(3, true)
            ->willReturn(50);

        $result = ($this->command)($io, true, 3);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeWithInvalidMonth(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('error')
            ->with('Month must be greater or equal 1!');

        $io->expects($this->never())
            ->method('title');

        $this->purgeHelper->expects($this->never())
            ->method('purgeLogs');

        $result = ($this->command)($io, false, 0);

        $this->assertEquals(Command::FAILURE, $result);
    }
}
