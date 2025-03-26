<?php

namespace Svc\LogBundle\Service;

use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Exception\DailySummaryCannotSendMail;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotDefined;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotExists;
use Svc\LogBundle\Exception\DailySummaryDefinitionNotImplement;
use Svc\LogBundle\Exception\DailySummaryEmailNotDefined;
use Svc\LogBundle\Exception\DailySummaryEmailNotValid;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Settings\SvcLogSettings;
use Svc\UtilBundle\Service\MailerHelper;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class SummaryList
{
  /**
   * @param SvcLog[] $logItems
   */
  public function __construct(public string $title, public array $logItems)
  {
  }
}

class DailySummaryHelper
{
  private \DateTimeImmutable $startDate;

  private \DateTimeImmutable $endDate;

  public function __construct(
    private readonly DataProviderInterface $dataProvider,
    private readonly Environment $twig,
    private readonly SvcLogRepository $svcLogRep,
    private readonly MailerHelper $mailerHelper,
    private readonly SettingsManagerInterface $settingsManager,
    private readonly ValidatorInterface $validator,
    private readonly string $mailSubject,
    private readonly ?string $defClassName = null,
    private readonly ?string $destinationEmail = null,
  ) {
    $this->startDate = new \DateTimeImmutable('yesterday');
    $this->endDate = new \DateTimeImmutable('tomorrow');
  }

  public function mailSummary(): bool
  {
    if (!$this->destinationEmail) {
      throw new DailySummaryEmailNotDefined();
    }

    $emailConstraint = new Assert\Email();
    $errors = $this->validator->validate(
      $this->destinationEmail,
      $emailConstraint
    );

    if ($errors->count()) {
      throw new DailySummaryEmailNotValid();
    }

    $content = $this->createSummary();

    $result = $this->mailerHelper->send($this->destinationEmail, $this->mailSubject, $content);

    if (!$result) {
      throw new DailySummaryCannotSendMail();
    }

    $logSettings = $this->settingsManager->get(SvcLogSettings::class);
    $logSettings->setLastRunDailySummaryToNow();
    $this->settingsManager->save($logSettings);

    return true;
  }

  public function getSummary(): string
  {
    return $this->createSummary();
  }

  private function createSummary(): string
  {
    if (!$this->defClassName) {
      throw new DailySummaryDefinitionNotDefined();
    }

    if (!class_exists($this->defClassName)) {
      throw new DailySummaryDefinitionNotExists();
    }

    /**
     * @var DailySummaryDefinitionInterface
     */
    $defClass = new $this->defClassName();
    /* @phpstan-ignore instanceof.alwaysTrue */
    if (!($defClass instanceof DailySummaryDefinitionInterface)) {
      throw new DailySummaryDefinitionNotImplement();
    }

    $definitions = $defClass->getDefinition();

    $listData = [];
    $aggrData = [];
    $countDataSourceType = [];

    foreach ($definitions as $definition) {
      switch ($definition->summaryType) {
        case DailySummaryType::LIST:
          $logs = $this->handleLogList($definition);
          if ($logs or !$definition->hideWhenEmpty) {
            $listData[] = new SummaryList($definition->title, $logs);
          }
          break;

        case DailySummaryType::AGGR_LOG_LEVEL:
          $data = $this->handleAggrByLoglevel($definition);
          if ($data) {
            $aggrData[] = ['title' => $definition->title, 'data' => $data];
          }
          break;

        case DailySummaryType::COUNT_SOURCE_TYPE:
          $data = $this->handleCountSourceType($definition);
          if ($data) {
            $countDataSourceType[] = $data;
          }
          break;
      }
    }

    $result = $this->twig->render('@SvcLog/daily_summary/summary.html.twig', [
      'daily_lists' => $listData,
      'daily_aggrs' => $aggrData,
      'daily_counts_st' => $countDataSourceType,
      'header' => $this->mailSubject,
    ]);

    return $result;
  }

  /**
   * list for DailySummaryType::LIST.
   *
   * @return array<mixed>
   */
  private function handleLogList(DailySumDef $definition): array
  {
    $logs = $this->svcLogRep->getDailyLogDataList(
      $this->startDate,
      $this->endDate,
      $definition->sourceID,
      $definition->sourceType,
      $definition->logLevel,
      $definition->logLevelCompare,
    );

    foreach ($logs as $log) {
      // if ($log->getSourceType() >= 90000) { // internal handled sourceType
      //   $log->setSourceTypeText(LogAppConstants::getSourceTypeText($log->getSourceType()));
      //   $log->setSourceIDText((string) $log->getSourceID());
      // } else {
      $log->setSourceTypeText($this->dataProvider->getSourceTypeText($log->getSourceType()));
      $log->setSourceIDText($this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType()));
      //      }
    }

    return $logs;
  }

  /**
   * calculation for DailySummaryType::AGGR_LOG_LEVEL (aggregation by loglevel).
   *
   * @return array<mixed>
   */
  private function handleAggrByLoglevel(DailySumDef $definition): array
  {
    $data = $this->svcLogRep->getDailyAggrLogLevel(
      $this->startDate,
      $this->endDate,
      $definition->logLevel,
      $definition->logLevelCompare,
    );

    foreach ($data as $key => $line) {
      // if (array_key_exists($line['logLevel'], EventLog::ARR_LEVEL_TEXT)) {
      //   $data[$key]['logLevelText'] = EventLog::ARR_LEVEL_TEXT[$line['logLevel']];
      // } else {
      //   $data[$key]['logLevelText'] = '? (' . strval($line['logLevel']) . ')';
      // }
      $tempLog = new SvcLog();
      $tempLog->setLogLevel($line['logLevel']);
      $data[$key]['logLevelBGColor'] = $tempLog->getLogLevelBGColorHTML();
      $data[$key]['logLevelFGColor'] = $tempLog->getLogLevelFGColorHTML();
      $data[$key]['logLevelText'] = $tempLog->getLogLevelText();
    }

    return $data;
  }

  /**
   * calculation for DailySummaryType::COUNT_SOURCE_TYPE (count for a specific SOURCE_TYPE).
   *
   * @return array<mixed>
   */
  private function handleCountSourceType(DailySumDef $definition): array
  {
    if (!isset($definition->countSourceTypeDef)) {
      return [];
    }
    $dataFound = false;

    $data = [];
    $data['title'] = $definition->title;
    $data['data'] = [];

    foreach ($definition->countSourceTypeDef as $cntDef) {
      $rowcount = $this->svcLogRep->getDailyCountBySourceType(
        $this->startDate,
        $this->endDate,
        $cntDef['sourceType'],
        $cntDef['onlyHuman'] ?? false,
      );

      if (!$definition->hideWhenZero or $rowcount > 0) {
        $data['data'][] = ['item_title' => $cntDef['title'], 'item_count' => $rowcount];
        $dataFound = true;
      }
    }

    if ($dataFound or !$definition->hideWhenEmpty) {
      return $data;
    } else {
      return [];
    }
  }
}
