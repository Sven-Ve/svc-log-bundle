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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\BatchHelper;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowMockObjectsWithoutExpectations]
class BatchHelperTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    private SvcLogRepository&MockObject $logRepo;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logRepo = $this->createMock(SvcLogRepository::class);
        $this->io = $this->createStub(SymfonyStyle::class);
    }

    /**
     * check, if we load the correct class.
     */
    public function testClassLoad(): void
    {
        $helper = new BatchHelper(false, $this->entityManager, $this->logRepo);
        $this->assertInstanceOf(BatchHelper::class, $helper);
    }

    public function testBatchFillLocationThrowsIfIpSavingDisabled(): void
    {
        $helper = new BatchHelper(false, $this->entityManager, $this->logRepo);

        $this->expectException(DeleteAllLogsForbidden::class);
        $helper->batchFillLocation(false, $this->io);
    }

    public function testBatchFillLocationReturnsZeroIfNoEntries(): void
    {
        $this->logRepo->expects($this->once())
            ->method('findBy')
            ->with(['country' => null])
            ->willReturn([]);

        $helper = new BatchHelper(true, $this->entityManager, $this->logRepo);

        $result = $helper->batchFillLocation(false, $this->io);
        $this->assertSame(0, $result);
    }

    public function testBatchFillLocationSetsCountryForLocalhost(): void
    {
        $entry = $this->createMock(SvcLog::class);
        $entry->expects($this->exactly(2))->method('getIp')->willReturn('127.0.0.1');
        $entry->expects($this->once())->method('setCountry')->with('-');


        $this->logRepo->expects($this->once())
            ->method('findBy')
            ->with(['country' => null])
            ->willReturn([$entry]);

        $this->entityManager->expects($this->once())->method('flush');

        $helper = new BatchHelper(true, $this->entityManager, $this->logRepo);

        $result = $helper->batchFillLocation(false, $this->io);
        $this->assertSame(0, $result);
    }

    /*     public function testBatchFillLocationSetsCountryFromNetworkHelper(): void
        {
            $entry = $this->createMock(SvcLog::class);
            $entry->expects($this->any())->method('getIp')->willReturn('217.26.48.170'); // hostpoint.ch
            $entry->expects($this->once())->method('setCountry')->with('CH');
            $entry->expects($this->once())->method('setCity')->with('');

            $this->logRepo->expects($this->once())
                ->method('findBy')
                ->with(['country' => null])
                ->willReturn([$entry]);

            $this->entityManager->expects($this->once())->method('flush');

            // Patch NetworkHelper::getLocationInfoByIp statically
            // $networkHelper = $this->getMockBuilder('stdClass')
            //     ->addMethods(['getLocationInfoByIp'])
            //     ->getMock();
            // $networkHelper::staticExpects($this->any())
            //     ->method('getLocationInfoByIp')
            //     ->willReturn(['country' => 'Germany', 'city' => 'Berlin']);

            // Use runkit or uopz to mock static method if available, or skip this test if not possible

            $helper = new BatchHelper(true, $this->entityManager, $this->logRepo);

            // This will call the real static method unless patched
            $result = $helper->batchFillLocation(false, $this->io);
            $this->assertSame(1, $result);
        } */

    public function testBatchFillLocationHandlesExceptionOnFlush(): void
    {
        $entry = $this->createMock(SvcLog::class);
        $entry->expects($this->exactly(2))->method('getIp')->willReturn('127.0.0.1');
        $entry->expects($this->once())->method('setCountry')->with('-');

        $this->logRepo->expects($this->once())
            ->method('findBy')
            ->willReturn([$entry]);

        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('DB error'));

        $helper = new BatchHelper(true, $this->entityManager, $this->logRepo);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot save data: DB error');
        $helper->batchFillLocation(false, $this->io);
    }
}
