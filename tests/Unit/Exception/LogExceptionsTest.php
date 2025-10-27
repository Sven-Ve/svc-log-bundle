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

namespace Svc\LogBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Exception\IpSavingNotEnabledException;
use Svc\LogBundle\Exception\LogExceptionInterface;

class LogExceptionsTest extends TestCase
{
    public function testDeleteAllLogsForbiddenException(): void
    {
        $exception = new DeleteAllLogsForbidden();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(LogExceptionInterface::class, $exception);
        $this->assertSame('You cannot delete all logs, you need a filter.', $exception->getMessage());
        $this->assertSame('You cannot delete all logs, you need a filter.', $exception->getReason());
    }

    public function testDeleteAllLogsForbiddenWithCustomMessage(): void
    {
        $customMessage = 'Custom error message';
        $exception = new DeleteAllLogsForbidden($customMessage);

        $this->assertSame($customMessage, $exception->getMessage());
        // getReason() returns the same as getMessage() for this exception
        $this->assertSame($customMessage, $exception->getReason());
    }

    public function testIpSavingNotEnabledException(): void
    {
        $exception = new IpSavingNotEnabledException();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(LogExceptionInterface::class, $exception);
        $this->assertSame('IP saving is disabled. This function is not allowed.', $exception->getMessage());
        $this->assertSame('IP saving is disabled. This function is not allowed.', $exception->getReason());
    }

    public function testIpSavingNotEnabledWithCustomMessage(): void
    {
        $customMessage = 'Custom IP error message';
        $exception = new IpSavingNotEnabledException($customMessage);

        $this->assertSame($customMessage, $exception->getMessage());
        // getReason() returns the same as getMessage() for this exception
        $this->assertSame($customMessage, $exception->getReason());
    }

    public function testExceptionsCanBeThrown(): void
    {
        $this->expectException(DeleteAllLogsForbidden::class);
        $this->expectExceptionMessage('You cannot delete all logs, you need a filter.');

        throw new DeleteAllLogsForbidden();
    }

    public function testIpSavingExceptionCanBeThrown(): void
    {
        $this->expectException(IpSavingNotEnabledException::class);
        $this->expectExceptionMessage('IP saving is disabled. This function is not allowed.');

        throw new IpSavingNotEnabledException();
    }
}
