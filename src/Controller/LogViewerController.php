<?php

namespace Svc\LogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\EventLog;

/**
 * Controller for displaying and filtering the log
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 * 
 * @IsGranted("ROLE_ADMIN")
 */
class LogViewerController extends AbstractController
{

  private $dataProvider;
  public function __construct(DataProviderInterface $dataProvider)
  {
    $this->dataProvider = $dataProvider;
  }

  /**
   * show a log table (without data, only the construct)
   */
  public function viewTable(): Response
  {

    return $this->render('@SvcLog/log_viewer/viewer.html.twig', [
      'levelArray' => EventLog::ARR_LEVEL_TEXT,
      'init' => true
    ]);
  }

  /**
   * show a log table (the records)
   */
  public function viewData(Request $request, SvcLogRepository $svcLogRep): Response
  {

    $offset = $this->checkParam($request->query->get("offset")) ?? 0;
    $sourceID = $this->checkParam($request->query->get("sourceID"));
    $sourceIDC = $this->checkParam($request->query->get("sourceIDC"));
    $sourceType = $this->checkParam($request->query->get("sourceType"));
    $sourceTypeC = $this->checkParam($request->query->get("sourceTypeC"));
    $logLevel = $this->checkParam($request->query->get("logLevel"));
    $logLevelC = $this->checkParam($request->query->get("logLevelC"));
    $country = $request->query->get("country");



    $logs = $svcLogRep->getLogPaginatorForViewer($offset, $sourceID, $sourceIDC, $sourceType, $sourceTypeC, $logLevel, $logLevelC, $country);
    
    foreach ($logs as $log) {
      $log->sourceTypeText = $this->dataProvider->getSourceTypeText($log->getSourceType());
      $log->sourceIDText = $this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType());
    }

    $dataContr = [];
    $dataContr["next"] = min(count($logs), $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
    $dataContr["prev"] = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $dataContr["last"] = max(count($logs) - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $dataContr['hidePrev'] = $offset <= 0;
    $dataContr['hideNext'] = $offset >= count($logs) - SvcLogRepository::PAGINATOR_PER_PAGE;
    $dataContr['count'] = count($logs);
    $dataContr['from'] = $offset + 1;
    $dataContr['to'] = min($offset + SvcLogRepository::PAGINATOR_PER_PAGE, count($logs));


    return $this->render('@SvcLog/log_viewer/_table_rows.html.twig' , [
      'logs' => $logs,
      'dataContr' => $dataContr,
    ]);
  }

  /**
   * check a (numeric) url parameter
   *
   * @param string|null $value
   * @return integer|null
   */
  private function checkParam(?string $value): ?int
  {
    if ($value === null or $value === "") {
      return null;
    }
    return intval($value);
  }
}
