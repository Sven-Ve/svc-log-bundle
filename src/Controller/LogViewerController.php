<?php

namespace Svc\LogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\EventLog;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class LogViewerController extends AbstractController
{


  /**
   * show statistics for a video
   */
  public function view(Request $request, SvcLogRepository $svcLogRep): Response
  {

    $offset = $this->checkParam($request->query->get("offset")) ?? 0;
    $sourceID = $this->checkParam($request->query->get("sourceID"));
    $sourceIDC = $this->checkParam($request->query->get("sourceIDC"));
    $sourceType = $this->checkParam($request->query->get("sourceType"));
    $sourceTypeC = $this->checkParam($request->query->get("sourceTypeC"));
    $logLevel = $this->checkParam($request->query->get("logLevel"));
    $logLevelC = $this->checkParam($request->query->get("logLevelC"));
    $onlyData = $request->query->get("onlyData");



    $logs = $svcLogRep->getLogPaginatorForViewer($offset, $sourceID, $sourceIDC, $sourceType, $sourceTypeC, $logLevel, $logLevelC) ;

    $next = min(count($logs), $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
    $prev = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $last = max(count($logs) - SvcLogRepository::PAGINATOR_PER_PAGE, 0);


    $template = $onlyData ? "_table_rows.html.twig" : "viewer.html.twig";
    return $this->render('@SvcLog/log_viewer/' . $template, [
      'logs' => $logs,
      'sourceID' => $sourceID,
      'sourceType' => $sourceType,
      'logLevel' => $logLevel,
      'levelArray' => EventLog::ARR_LEVEL_TEXT,
      'next' => $next,
      'prev' => $prev,
      'last' => $last,
    ]);
  }


  private function checkParam(?string $value): ?int {
    if ($value === null or $value==="") {
      return null;
    } 
    return intval($value);
  }

  
}
