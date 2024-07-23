<?php

namespace Svc\LogBundle\Service;

use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Enum\DailySummaryType;
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
   * @param \Svc\LogBundle\Entity\SvcLog[] $logItems
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
    $this->mailerHelper->send($this->destinationEmail, $this->mailSubject, $content);

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
          $logs = $this->svcLogRep->getDailyLogDataList(
            $this->startDate,
            $this->endDate,
            $definition->sourceID,
            $definition->sourceType,
            $definition->logLevel,
            $definition->logLevelCompare,
          );

          foreach ($logs as $log) {
            $log->setSourceTypeText($this->dataProvider->getSourceTypeText($log->getSourceType()));
            $log->setSourceIDText($this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType()));
          }

          $listData[] = new SummaryList($definition->title, $logs);
          break;

        case DailySummaryType::AGGR_LOG_LEVEL:
          $data = $this->svcLogRep->getDailyAggrLogLevel(
            $this->startDate,
            $this->endDate,
            $definition->logLevel,
            $definition->logLevelCompare,
          );

          foreach ($data as $key => $line) {
            if (array_key_exists($line['logLevel'], EventLog::ARR_LEVEL_TEXT)) {
              $data[$key]['logLevelText'] = EventLog::ARR_LEVEL_TEXT[$line['logLevel']];
            } else {
              $data[$key]['logLevelText'] = '? (' . strval($line['logLevel']) . ')';
            }
          }
          $aggrData[] = ['title' => $definition->title, 'data' => $data];

          break;

        case DailySummaryType::COUNT_SOURCE_TYPE:
          $data = $this->handleCountSourceType($definition);
          if ($data) {
            $countDataSourceType[] = $data;
          }

          break;
      }
    }

    $result = $this->twig->render('@SvcLog/daily_summary/index.html.twig', [
      'daily_lists' => $listData,
      'daily_aggrs' => $aggrData,
      'daily_counts_st' => $countDataSourceType,
    ]);

    return $result;
  }

  /**
   * @return array<mixed>
   */
  private function handleCountSourceType(DailySumDef $definition): array
  {
    if (!isset($definition->countSourceTypeDef)) {
      return [];
    }

    $data = [];
    $data['title'] = $definition->title;
    $data['data'] = [];

    foreach ($definition->countSourceTypeDef as $cntDef) {
      $rowcount = $this->svcLogRep->getDailyCountBySourceType(
        $this->startDate,
        $this->endDate,
        $cntDef['sourceType'], /* @phpstan-ignore offsetAccess.nonOffsetAccessible */
      );

      /* @phpstan-ignore offsetAccess.nonOffsetAccessible */
      $data['data'][] = ['item_title' => $cntDef['title'], 'item_count' => $rowcount];
    }

    return $data;
  }
}
