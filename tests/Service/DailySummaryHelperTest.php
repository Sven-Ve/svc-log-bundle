<?php

namespace Svc\LogBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Svc\LogBundle\Service\DailySummaryHelper;
use Svc\LogBundle\Service\SummaryList;
use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\UtilBundle\Service\MailerHelper;
use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use PHPUnit\Event\Event;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Exception\DailySummaryEmailNotDefined;
use Svc\LogBundle\Exception\DailySummaryEmailNotValid;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotDefined;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotExists;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotImplement;
use Svc\LogBundle\Exception\DailySummaryCannotSendMail;
use Svc\LogBundle\Service\EventLog;
use Symfony\Component\Validator\ConstraintViolationList;

class DummyDef implements \Svc\LogBundle\Service\DailySummaryDefinitionInterface
{
    public function getDefinition(): array
    {
        $def = new DailySumDef('Test List', DailySummaryType::LIST);
        $def->hideWhenEmpty = false;
        return [$def];
    }
}

class DailySummaryHelperTest extends TestCase
{
    private function getHelper(array $options = [], $setDefClassName = true)
    {
        $dataProvider = $options['dataProvider'] ?? $this->createMock(DataProviderInterface::class);
        $twig = $options['twig'] ?? $this->createMock(Environment::class);
        $svcLogRep = $options['svcLogRep'] ?? $this->createMock(SvcLogRepository::class);
        $mailerHelper = $options['mailerHelper'] ?? $this->createMock(MailerHelper::class);
        $settingsManager = $options['settingsManager'] ?? $this->createMock(SettingsManagerInterface::class);
        $validator = $options['validator'] ?? $this->createMock(ValidatorInterface::class);
        $eventLog = $options['eventLog'] ?? $this->createMock(EventLog::class);
        #$this->getMockBuilder(\stdClass::class)->addMethods(['writeLog'])->getMock();
        $mailSubject = $options['mailSubject'] ?? 'Test Subject';
        if ($setDefClassName) {
            $defClassName = $options['defClassName'] ?? DummyDef::class;
        } else {
            $defClassName = null;
        }
//        $defClassName = $options['defClassName'] ?? DummyDef::class;
        $destinationEmail = $options['destinationEmail'] ?? null;

        return new DailySummaryHelper(
            $dataProvider,
            $twig,
            $svcLogRep,
            $mailerHelper,
            $settingsManager,
            $validator,
            $eventLog,
            $mailSubject,
            $defClassName,
            $destinationEmail
        );
    }

    public function testMailSummaryThrowsIfNoEmail()
    {
        return;
        $helper = $this->getHelper(['destinationEmail' => null]);
        $this->expectException(DailySummaryEmailNotDefined::class);
        $helper->mailSummary();
    }

    
    public function testMailSummaryThrowsIfInvalidEmail()
    {
        return;
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList([ 
            new \Symfony\Component\Validator\ConstraintViolation(
                'Invalid email',
                'Invalid email',
                [],
                '',
                '',
                'invalid'
            )
        ]));
        $helper = $this->getHelper(['validator' => $validator, 'destinationEmail' => 'invalid']);
        $this->expectException(DailySummaryEmailNotValid::class);
        $helper->mailSummary();
    }


    public function testMailSummaryThrowsIfCannotSend()
    {
        return;
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList([]));
        $mailerHelper = $this->createMock(MailerHelper::class);
        $mailerHelper->method('send')->willReturn(false);
        $mailerHelper->method('getLastSendError')->willReturn('error');
        $eventLog = $this->createMock(EventLog::class);
        $eventLog->expects($this->once())->method('writeLog');
        $helper = $this->getHelper([
            'validator' => $validator,
            'mailerHelper' => $mailerHelper,
            'eventLog' => $eventLog,
            'destinationEmail' => 'valid@example.com'
        ]);
        $this->expectException(DailySummaryCannotSendMail::class);
        $helper->mailSummary();
    }
    /*
    public function testMailSummarySuccess()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new \ArrayObject([]));
        $mailerHelper = $this->createMock(MailerHelper::class);
        $mailerHelper->method('send')->willReturn(true);
        $settings = $this->getMockBuilder(\stdClass::class)->addMethods(['setLastRunDailySummaryToNow'])->getMock();
        $settingsManager = $this->createMock(SettingsManagerInterface::class);
        $settingsManager->method('get')->willReturn($settings);
        $settingsManager->expects($this->once())->method('save');
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('summary');
        $helper = $this->getHelper([
            'validator' => $validator,
            'mailerHelper' => $mailerHelper,
            'settingsManager' => $settingsManager,
            'twig' => $twig,
        ]);
        $this->assertTrue($helper->mailSummary());
    }
*/
    public function testGetSummaryReturnsString()
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('summary');
        $helper = $this->getHelper(['twig' => $twig]);
        $this->assertSame('summary', $helper->getSummary());
    }

    public function testCreateSummaryThrowsIfNoDefClass()
    {
        $helper = $this->getHelper([], setDefClassName: false);
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('createSummary');
        $method->setAccessible(true);
        $this->expectException(DailySummaryDefinitionNotDefined::class);
        $method->invoke($helper);
    }


    public function testCreateSummaryThrowsIfDefClassNotExists()
    {
        $helper = $this->getHelper(['defClassName' => 'NotAClass']);
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('createSummary');
        $method->setAccessible(true);
        $this->expectException(DailySummaryDefinitionNotExists::class);
        $method->invoke($helper);
    }

    public function testCreateSummaryThrowsIfDefClassNotImplements()
    {
        $helper = $this->getHelper(['defClassName' => \stdClass::class]);
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('createSummary');
        $method->setAccessible(true);
        $this->expectException(DailySummaryDefinitionNotImplement::class);
        $method->invoke($helper);
    }
    /*
    public function testHandleLogListReturnsLogs()
    {
        $svcLogRep = $this->createMock(SvcLogRepository::class);
        $log = $this->createMock(SvcLog::class);
        $log->method('setSourceTypeText');
        $log->method('setSourceIDText');
        $svcLogRep->method('getDailyLogDataList')->willReturn([$log]);
        $dataProvider = $this->createMock(DataProviderInterface::class);

        $dataProvider->method('getSourceType')->willReturn(1);
        $dataProvider->method('getSourceTypeText')->willReturn("1");
        $dataProvider->method('getSourceIDText')->willReturn("1");
        $helper = $this->getHelper(['svcLogRep' => $svcLogRep, 'dataProvider' => $dataProvider]);
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('handleLogList');
        $method->setAccessible(true);
        $def = new DailySumDef('Test', DailySummaryType::LIST);
        $def->hideWhenEmpty = false;
        $result = $method->invoke($helper, $def);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testHandleAggrByLoglevelReturnsData()
    {
        $svcLogRep = $this->createMock(SvcLogRepository::class);
        $svcLogRep->method('getDailyAggrLogLevel')->willReturn([['logLevel' => 1]]);
        $helper = $this->getHelper(['svcLogRep' => $svcLogRep]);
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('handleAggrByLoglevel');
        $method->setAccessible(true);
        $def = new DailySumDef();
        $def->summaryType = DailySummaryType::AGGR_LOG_LEVEL;
        $result = $method->invoke($helper, $def);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('logLevelText', $result[0]);
    }

    public function testHandleCountSourceTypeReturnsData()
    {
        $svcLogRep = $this->createMock(SvcLogRepository::class);
        $svcLogRep->method('getDailyCountBySourceType')->willReturn(2);
        $helper = $this->getHelper(['svcLogRep' => $svcLogRep]);
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('handleCountSourceType');
        $method->setAccessible(true);
        $def = new DailySumDef();
        $def->summaryType = DailySummaryType::COUNT_SOURCE_TYPE;
        $def->title = 'Count';
        $def->hideWhenZero = false;
        $def->hideWhenEmpty = false;
        $def->countSourceTypeDef = [['sourceType' => 1, 'title' => 'A']];
        $result = $method->invoke($helper, $def);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('data', $result);
    }
        */
}