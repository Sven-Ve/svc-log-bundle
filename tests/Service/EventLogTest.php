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

namespace Svc\LogBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Service\LoggerHelper;
use Symfony\Bundle\SecurityBundle\Security;

class EventLogTest extends TestCase
{
    private EventLog $eventLog;

    private EntityManagerInterface&MockObject $entityManager;

    private LoggerHelper&MockObject $loggerHelper;

    private Security&MockObject $security;

    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->loggerHelper = $this->createMock(LoggerHelper::class);
        $this->security = $this->createMock(Security::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->eventLog = new EventLog(
            enableIPSaving: true,
            enableUserSaving: true,
            minLogLevel: LogLevel::DEBUG,
            enableLogger: true,
            loggerMinLogLevel: LogLevel::INFO,
            disable404Logger: false,
            security: $this->security,
            entityManager: $this->entityManager,
            loggerHelper: $this->loggerHelper,
            managerRegistry: $this->managerRegistry
        );
    }

    public function testWriteLogReturnsTrueWhenBelowMinLevel(): void
    {
        $eventLog = new EventLog(
            enableIPSaving: false,
            enableUserSaving: false,
            minLogLevel: LogLevel::ERROR,
            enableLogger: false,
            loggerMinLogLevel: LogLevel::ERROR,
            disable404Logger: false,
            security: $this->security,
            entityManager: $this->entityManager,
            loggerHelper: $this->loggerHelper,
            managerRegistry: $this->managerRegistry
        );

        $this->entityManager->expects($this->never())->method('persist');

        $result = $eventLog->writeLog(1, 1, LogLevel::DEBUG, 'Test message');
        $this->assertTrue($result);
    }

    public function testWriteLogBasicFunctionality(): void
    {
        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(SvcLog::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $result = $this->eventLog->writeLog(
            sourceID: 123,
            sourceType: 456,
            level: LogLevel::INFO,
            message: 'Test message',
            errorText: 'Test error'
        );

        $this->assertTrue($result);
    }

    public function testWriteLogHandlesUserException(): void
    {
        $this->security->expects($this->once())
            ->method('getUser')
            ->willThrowException(new \Exception('User error'));

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $result = $this->eventLog->writeLog(1, 1, LogLevel::INFO, 'Test');
        $this->assertTrue($result);
    }

    public function testWriteLogHandlesDatabaseException(): void
    {
        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('DB error'));

        $result = $this->eventLog->writeLog(1, 1, LogLevel::INFO, 'Test');
        $this->assertFalse($result);
    }

    public function testWriteLogHandlesClosedEntityManager(): void
    {
        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        $this->managerRegistry->expects($this->once())
            ->method('resetManager');

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $result = $this->eventLog->writeLog(1, 1, LogLevel::INFO, 'Test');
        $this->assertTrue($result);
    }

    public function testWriteLogSkipsLoggerForDataLevel(): void
    {
        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->never())
            ->method('send');

        $result = $this->eventLog->writeLog(1, 1, LogLevel::DATA, 'Test');
        $this->assertTrue($result);
    }

    public function testWriteLogSkipsLoggerWhen404Disabled(): void
    {
        $eventLog = new EventLog(
            enableIPSaving: false,
            enableUserSaving: false,
            minLogLevel: LogLevel::DEBUG,
            enableLogger: true,
            loggerMinLogLevel: LogLevel::INFO,
            disable404Logger: true,
            security: $this->security,
            entityManager: $this->entityManager,
            loggerHelper: $this->loggerHelper,
            managerRegistry: $this->managerRegistry
        );

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->never())
            ->method('send');

        $result = $eventLog->writeLog(1, 1, LogLevel::ERROR, 'Test', null, 404);
        $this->assertTrue($result);
    }

    public function testWriteLogHandlesLoggerFailure(): void
    {
        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->once())
            ->method('send')
            ->willReturn(false);

        $result = $this->eventLog->writeLog(1, 1, LogLevel::ERROR, 'Test');
        $this->assertFalse($result);
    }

    public function testWriteLogTruncatesLongMessage(): void
    {
        $longMessage = str_repeat('a', 300);

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (SvcLog $log) {
                return strlen($log->getMessage()) === 254;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $result = $this->eventLog->writeLog(1, 1, LogLevel::INFO, $longMessage);
        $this->assertTrue($result);
    }

    public function testWriteLogTruncatesLongErrorText(): void
    {
        $longErrorText = str_repeat('b', 300);

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (SvcLog $log) {
                return strlen($log->getErrorText()) === 254;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->loggerHelper->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $result = $this->eventLog->writeLog(1, 1, LogLevel::INFO, 'Test', $longErrorText);
        $this->assertTrue($result);
    }

    public function testGetLevelsForChoicesWithoutAll(): void
    {
        $choices = EventLog::getLevelsForChoices(false);

        $this->assertArrayNotHasKey(0, $choices);
        $this->assertCount(count(LogLevel::cases()), $choices);

        foreach (LogLevel::cases() as $level) {
            $this->assertArrayHasKey($level->value, $choices);
            $this->assertEquals($level->label(), $choices[$level->value]);
        }
    }

    public function testGetLevelsForChoicesWithAll(): void
    {
        $choices = EventLog::getLevelsForChoices(true);

        $this->assertArrayHasKey(0, $choices);
        $this->assertEquals('all', $choices[0]);
        $this->assertCount(count(LogLevel::cases()) + 1, $choices);
    }

    public function testWriteLogWithIPSavingDisabled(): void
    {
        $eventLog = new EventLog(
            enableIPSaving: false,
            enableUserSaving: false,
            minLogLevel: LogLevel::DEBUG,
            enableLogger: false,
            loggerMinLogLevel: LogLevel::ERROR,
            disable404Logger: false,
            security: $this->security,
            entityManager: $this->entityManager,
            loggerHelper: $this->loggerHelper,
            managerRegistry: $this->managerRegistry
        );

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (SvcLog $log) {
                return $log->getIp() === null;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $eventLog->writeLog(1, 1, LogLevel::INFO, 'Test');
        $this->assertTrue($result);
    }

    public function testWriteLogWithUserSavingDisabled(): void
    {
        $eventLog = new EventLog(
            enableIPSaving: false,
            enableUserSaving: false,
            minLogLevel: LogLevel::DEBUG,
            enableLogger: false,
            loggerMinLogLevel: LogLevel::ERROR,
            disable404Logger: false,
            security: $this->security,
            entityManager: $this->entityManager,
            loggerHelper: $this->loggerHelper,
            managerRegistry: $this->managerRegistry
        );

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(SvcLog::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->security->expects($this->never())
            ->method('getUser');

        $result = $eventLog->writeLog(1, 1, LogLevel::INFO, 'Test');
        $this->assertTrue($result);
    }
}
