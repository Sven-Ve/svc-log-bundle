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
    $country = $request->query->get("country");
    $onlyData = $request->query->get("onlyData");



    $logs = $svcLogRep->getLogPaginatorForViewer($offset, $sourceID, $sourceIDC, $sourceType, $sourceTypeC, $logLevel, $logLevelC, $country) ;

    $dataContr = [];
    $dataContr["next"] = min(count($logs), $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
    $dataContr["prev"] = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $dataContr["last"] = max(count($logs) - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $dataContr['hidePrev'] = $offset <= 0;
    $dataContr['hideNext'] = $offset >= count($logs) - SvcLogRepository::PAGINATOR_PER_PAGE;


    $template = $onlyData ? "_table_rows.html.twig" : "viewer.html.twig";
    return $this->render('@SvcLog/log_viewer/' . $template, [
      'logs' => $logs,
      'sourceID' => $sourceID,
      'sourceType' => $sourceType,
      'logLevel' => $logLevel,
      'country' => $country,
      'levelArray' => EventLog::ARR_LEVEL_TEXT,
      'dataContr' => $dataContr,
    ]);
  }


  private function checkParam(?string $value): ?int {
    if ($value === null or $value==="") {
      return null;
    } 
    return intval($value);
  }

  
}
