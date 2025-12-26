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
use Svc\LogBundle\Command\BatchFillLocationCommand;
use Svc\LogBundle\Exception\IpSavingNotEnabledException;
use Svc\LogBundle\Service\BatchHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowMockObjectsWithoutExpectations]
class BatchFillLocationCommandTest extends TestCase
{
    private BatchFillLocationCommand $command;

    private BatchHelper&MockObject $batchHelper;

    protected function setUp(): void
    {
        $this->batchHelper = $this->createMock(BatchHelper::class);
        $this->command = new BatchFillLocationCommand($this->batchHelper);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(BatchFillLocationCommand::class, $this->command);
    }

    public function testInvokeSuccessWithoutForce(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Fill country and city for event logs');

        $io->expects($this->once())
            ->method('success')
            ->with('42 locations set');

        $this->batchHelper->expects($this->once())
            ->method('batchFillLocation')
            ->with(false, $io)
            ->willReturn(42);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeSuccessWithForce(): void
    {
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('title')
            ->with('Fill country and city for event logs');

        $io->expects($this->once())
            ->method('success')
            ->with('100 locations set');

        $this->batchHelper->expects($this->once())
            ->method('batchFillLocation')
            ->with(true, $io)
            ->willReturn(100);

        $result = $this->command->__invoke($io, force: true);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testInvokeWithException(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $exception = new IpSavingNotEnabledException();

        $io->expects($this->once())
            ->method('title')
            ->with('Fill country and city for event logs');

        $io->expects($this->once())
            ->method('error')
            ->with($exception->getReason());

        $this->batchHelper->expects($this->once())
            ->method('batchFillLocation')
            ->with(false, $io)
            ->willThrowException($exception);

        $result = $this->command->__invoke($io);

        $this->assertEquals(Command::FAILURE, $result);
    }
}
