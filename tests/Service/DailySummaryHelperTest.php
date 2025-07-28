<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Tests\Service;

use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotDefined;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotExists;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotImplement;
use Svc\LogBundle\Exception\DailySummaryEmailNotDefined;
use Svc\LogBundle\Exception\DailySummaryEmailNotValid;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\DailySummaryDefinitionInterface;
use Svc\LogBundle\Service\DailySummaryHelper;
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Settings\SvcLogSettings;
use Svc\UtilBundle\Service\MailerHelper;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class MockDailySummaryDefinition implements DailySummaryDefinitionInterface
{
    public function getDefinition(): array
    {
        return [
            new DailySumDef('Test List', DailySummaryType::LIST),
        ];
    }
}

class DailySummaryHelperTest extends TestCase
{
    private DailySummaryHelper $helper;

    private MockObject $dataProvider;

    private MockObject $twig;

    private MockObject $svcLogRep;

    private MockObject $mailerHelper;

    private MockObject $settingsManager;

    private MockObject $validator;

    private MockObject $eventLog;

    protected function setUp(): void
    {
        $this->dataProvider = $this->createMock(DataProviderInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->svcLogRep = $this->createMock(SvcLogRepository::class);
        $this->mailerHelper = $this->createMock(MailerHelper::class);
        $this->settingsManager = $this->createMock(SettingsManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->eventLog = $this->createMock(EventLog::class);

        $this->helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            MockDailySummaryDefinition::class,
            'test@example.com'
        );
    }

    public function testMailSummaryThrowsExceptionWhenEmailNotDefined(): void
    {
        $helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            MockDailySummaryDefinition::class,
            null // No email
        );

        $this->expectException(DailySummaryEmailNotDefined::class);
        $helper->mailSummary();
    }

    public function testMailSummaryThrowsExceptionWhenEmailInvalid(): void
    {
        $helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            MockDailySummaryDefinition::class,
            'invalid-email'
        );

        $violations = new ConstraintViolationList();
        $violations->add($this->createMock(\Symfony\Component\Validator\ConstraintViolationInterface::class));

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(DailySummaryEmailNotValid::class);
        $helper->mailSummary();
    }

    public function testMailSummarySuccess(): void
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->twig->expects($this->once())
            ->method('render')
            ->willReturn('<html>Test Summary</html>');

        $this->svcLogRep->expects($this->once())
            ->method('getDailyLogDataList')
            ->willReturn([]);

        $this->mailerHelper->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $mockSettings = $this->createMock(SvcLogSettings::class);
        $mockSettings->expects($this->once())
            ->method('setLastRunDailySummaryToNow');

        $this->settingsManager->expects($this->once())
            ->method('get')
            ->willReturn($mockSettings);

        $this->settingsManager->expects($this->once())
            ->method('save');

        $result = $this->helper->mailSummary();
        $this->assertTrue($result);
    }

    public function testGetSummaryWithoutDefinitionClass(): void
    {
        $helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            null // No definition class
        );

        $this->expectException(DailySummaryDefinitionNotDefined::class);
        $helper->getSummary();
    }

    public function testGetSummaryWithNonExistentClass(): void
    {
        $helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            'NonExistentClass'
        );

        $this->expectException(DailySummaryDefinitionNotExists::class);
        $helper->getSummary();
    }

    public function testGetSummaryWithInvalidClass(): void
    {
        $helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            \stdClass::class // Valid class but doesn't implement interface
        );

        $this->expectException(DailySummaryDefinitionNotImplement::class);
        $helper->getSummary();
    }

    public function testGetSummaryWithMaliciousClassName(): void
    {
        $helper = new DailySummaryHelper(
            $this->dataProvider,
            $this->twig,
            $this->svcLogRep,
            $this->mailerHelper,
            $this->settingsManager,
            $this->validator,
            $this->eventLog,
            'Test Subject',
            'System("rm -rf /");' // Malicious class name
        );

        $this->expectException(DailySummaryDefinitionNotExists::class);
        $helper->getSummary();
    }

    public function testGetSummarySuccess(): void
    {
        $mockLog = new SvcLog();
        $mockLog->setSourceID(1);
        $mockLog->setSourceType(1);

        $this->svcLogRep->expects($this->once())
            ->method('getDailyLogDataList')
            ->willReturn([$mockLog]);

        $this->dataProvider->expects($this->any())
            ->method('getSourceTypeText')
            ->with(1)
            ->willReturn('Test Type');

        $this->dataProvider->expects($this->any())
            ->method('getSourceIDText')
            ->with(1, 1)
            ->willReturn('Test ID');

        $this->twig->expects($this->once())
            ->method('render')
            ->willReturn('<html>Test Summary</html>');

        $result = $this->helper->getSummary();
        $this->assertStringContainsString('Test Summary', $result);
    }
}
