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

    $offet = $this->checkParam($request->query->get("offset")) ?? 0;
    $sourceID = $this->checkParam($request->query->get("sourceID"));
    $sourceType = $this->checkParam($request->query->get("sourceType"));
    $logLevel = $this->checkParam($request->query->get("logLevel"));
    $onlyData = $request->query->get("onlyData");


    $logs = $svcLogRep->getLogPaginatorForViewer($offet, $sourceID, $sourceType, $logLevel) ;
    $template = $onlyData ? "_table_rows.html.twig" : "viewer.html.twig";
    return $this->render('@SvcLog/log_viewer/' . $template, [
      'logs' => $logs,
      'sourceID' => $sourceID,
      'sourceType' => $sourceType,
      'logLevel' => $logLevel,
      'levelArray' => EventLog::ARR_LEVEL_TEXT
    ]);
  }


  private function checkParam(?string $value): ?int {
    if ($value === null or $value==="") {
      return null;
    } 
    return intval($value);
  }

  
}
