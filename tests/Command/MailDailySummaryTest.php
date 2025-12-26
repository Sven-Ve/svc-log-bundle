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
use Svc\LogBundle\Command\MailDailySummary;
use Svc\LogBundle\Service\DailySummaryHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowMockObjectsWithoutExpectations]
class MailDailySummaryTest extends TestCase
{
    private MailDailySummary $command;

    private DailySummaryHelper&MockObject $dailySummaryHelper;

    protected function setUp(): void
    {
        $this->dailySummaryHelper = $this->createMock(DailySummaryHelper::class);
        $this->command = new MailDailySummary($this->dailySummaryHelper);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(MailDailySummary::class, $this->command);
    }

    public function testInvokeSuccess(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('success')
            ->with('Daily summary created successfully.');

        $io->expects($this->never())
            ->method('error');

        $this->dailySummaryHelper->expects($this->once())
            ->method('mailSummary')
            ->willReturn(true);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeFailure(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->never())
            ->method('success');

        $io->expects($this->once())
            ->method('error')
            ->with('Error during creating of Daily summary mail.');

        $this->dailySummaryHelper->expects($this->once())
            ->method('mailSummary')
            ->willReturn(false);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testInvokeWithException(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $exception = new \Exception('Test exception message');

        $io->expects($this->never())
            ->method('success');

        $io->expects($this->exactly(2))
            ->method('error')
            ->with($this->logicalOr(
                'Test exception message',
                'Error during creating of Daily summary mail.'
            ));

        $this->dailySummaryHelper->expects($this->once())
            ->method('mailSummary')
            ->willThrowException($exception);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testInvokeWithRuntimeException(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $exception = new \RuntimeException('Runtime error occurred');

        $io->expects($this->never())
            ->method('success');

        $io->expects($this->exactly(2))
            ->method('error')
            ->with($this->logicalOr(
                'Runtime error occurred',
                'Error during creating of Daily summary mail.'
            ));

        $this->dailySummaryHelper->expects($this->once())
            ->method('mailSummary')
            ->willThrowException($exception);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::FAILURE, $result);
    }
}
