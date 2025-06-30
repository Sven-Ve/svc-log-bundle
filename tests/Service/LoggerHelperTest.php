<?php

namespace Svc\LogBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Service\LoggerHelper;




class LoggerHelperTest extends TestCase
{
    private LoggerInterface $loggerMock;
    private LoggerHelper $loggerHelper;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->loggerHelper = new LoggerHelper($this->loggerMock);
    }

    /*
    private function createLogMock(array $methods = [])
    {
        $log = $this->createMock(SvcLog::class);
        $log->method('getLogLevel')->willReturn(LogLevel::INFO);
        $log->method('getLogLevelText')->willReturn('Info');
        $log->method('getMessage')->willReturn('Test message');
        $log->method('getUserName')->willReturn(null);
        $log->method('getSourceType')->willReturn(null);
        $log->method('getSourceID')->willReturn(null);
        $log->method('getIp')->willReturn(null);
        $log->method('getPlatform')->willReturn(null);
        $log->method('getReferer')->willReturn(null);
        $log->method('getBrowser')->willReturn(null);
        $log->method('getBrowserVersion')->willReturn(null);
        $log->method('getOs')->willReturn(null);
        $log->method('getOsVersion')->willReturn(null);
        $log->method('getErrorText')->willReturn(null);

        foreach ($methods as $method => $value) {
            $log->method($method)->willReturn($value);
        }

        return $log;
    }
    */

    public function testSendReturnsTrueOnSuccess()
    {
        $log = $this->createMock(SvcLog::class);
        $log->method('getLogLevel')->willReturn(LogLevel::INFO);
        $log->method('getLogLevelText')->willReturn('Info');
        $log->method('getMessage')->willReturn('Test message');

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with(
                'Test message',
                $this->arrayHasKey('sender')
            );

        $result = $this->loggerHelper->send($log);
        $this->assertTrue($result);
    }


    public function testSendReturnsFalseOnException()
    {
        $log = $this->createMock(SvcLog::class);
        $log->method('getLogLevel')->willReturn(LogLevel::INFO);
        $log->method('getLogLevelText')->willReturn('Info');
        $log->method('getMessage')->willReturn('Test message');

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->willThrowException(new \Exception());

        $result = $this->loggerHelper->send($log);
        $this->assertFalse($result);
    }


    public function testSendWithAllExtraFields()
    {
        // Create a mock SvcLog object with all fields set
        /** @var SvcLog $log */
        $log = $this->createMock(SvcLog::class);
        $log->method('getLogLevel')->willReturn(LogLevel::INFO);
        $log->method('getLogLevelText')->willReturn('Info');
        $log->method('getMessage')->willReturn('Test message');
        $log->method('getUserName')->willReturn('user1');
        $log->method('getSourceType')->willReturn(234);
        $log->method('getSourceID')->willReturn(123);
        $log->method('getIp')->willReturn('127.0.0.1');
        $log->method('getPlatform')->willReturn('web');
        $log->method('getReferer')->willReturn('http://example.com');
        $log->method('getBrowser')->willReturn('Firefox');
        $log->method('getBrowserVersion')->willReturn('89.0');
        $log->method('getOs')->willReturn('Linux');
        $log->method('getOsVersion')->willReturn('5.10');
        $log->method('getErrorText')->willReturn('Some error');


        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with(
                'Test message',
                [
                    'sender' => 'svc_log',
                    'log_level' => '2 Info',
                    'user' => 'user1',
                    'source_type' => 234,
                    'source_id' => 123,
                    'ip' => '127.0.0.1',
                    'platform' => 'web',
                    'referer' => 'http://example.com',
                    'browser' => 'Firefox 89.0',
                    'os' => 'Linux 5.10',
                    'errorText' => 'Some error',
                ]
            )
        ;

        $result = $this->loggerHelper->send($log);
        $this->assertTrue($result);
    }


    /**
     * @dataProvider logLevelProvider
     */
    /*
     public function testSendUsesCorrectLogLevel(LogLevel $level, string $expectedMethod)
    {
        $log = $this->createLogMock([
            'getLogLevel' => $level,
            'getLogLevelText' => $level->name,
        ]);

        $this->loggerMock
            ->expects($this->once())
            ->method($expectedMethod)
            ->with(
                $this->anything(),
                $this->arrayHasKey('sender')
            );

        $result = $this->loggerHelper->send($log);
        $this->assertTrue($result);
    }

    public static function logLevelProvider(): array
    {
        return [
            [LogLevel::DEBUG, 'debug'],
            [LogLevel::INFO, 'info'],
            [LogLevel::WARN, 'warning'],
            [LogLevel::ERROR, 'error'],
            [LogLevel::CRITICAL, 'critical'],
            [LogLevel::ALERT, 'alert'],
            [LogLevel::EMERGENCY, 'emergency'],
        ];
    }

    public function testSendUsesLogLevelTextIfMessageIsNull()
    {
        $log = $this->createLogMock();
        $log->method('getMessage')->willReturn(null);
        $log->method('getLogLevelText')->willReturn('InfoText');

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with(
                'InfoText',
                $this->arrayHasKey('sender')
            );

        $result = $this->loggerHelper->send($log);
        $this->assertTrue($result);
    }
*/
}
