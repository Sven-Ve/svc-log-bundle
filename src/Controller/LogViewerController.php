<?php

namespace Svc\LogBundle\Controller;

use Svc\LogBundle\DataProvider\DataProviderInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\EventLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for displaying and filtering the log.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class LogViewerController extends AbstractController
{
  public function __construct(
    private DataProviderInterface $dataProvider,
    private bool $enableUserSaving,
    private bool $enableIPSaving,
    private bool $needAdminForView,
  ) {
  }

  /**
   * show a log table (without data, only the construct).
   */
  public function viewTable(): Response
  {
    if ($this->needAdminForView) {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }

    return $this->render('@SvcLog/log_viewer/viewer.html.twig', [
      'levelArray' => EventLog::ARR_LEVEL_TEXT,
    ]);
  }

  /**
   * show a log table (the records).
   */
  public function viewData(Request $request, SvcLogRepository $svcLogRep): Response
  {
    if ($this->needAdminForView) {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }

    $offset = $request->query->getInt('offset');
    $sourceID = $this->checkParam($request->query->getString('sourceID'));
    $sourceIDC = $this->checkParam($request->query->getString('sourceIDC'));
    $sourceType = $this->checkParam($request->query->getString('sourceType'));
    $sourceTypeC = $this->checkParam($request->query->getString('sourceTypeC'));
    $logLevel = $this->checkParam($request->query->getString('logLevel'));
    $logLevelC = $this->checkParam($request->query->getString('logLevelC'));
    $country = $request->query->getString('country');
    $hideSourceCols = $this->checkParam($request->query->getString('hideSourceCols')) ?? 0;

    $logs = $svcLogRep->getLogPaginatorForViewer($offset, $sourceID, $sourceIDC, $sourceType, $sourceTypeC, $logLevel, $logLevelC, $country);

    if (!$hideSourceCols) {
      foreach ($logs as $log) {
        //   if ($log->getSourceType() >= 90000) { // internal handled sourceType
        //     $log->setSourceTypeText(AppConstants::getSourceTypeText($log->getSourceType()));
        //     $log->setSourceIDText($log->getSourceID());
        //   } else {
        $log->setSourceTypeText($this->dataProvider->getSourceTypeText($log->getSourceType()));
        $log->setSourceIDText($this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType()));
        // }
      }
    }

    $dataContr = [];
    $dataContr['next'] = min(is_countable($logs) ? count($logs) : 0, $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
    $dataContr['prev'] = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $dataContr['last'] = max((is_countable($logs) ? count($logs) : 0) - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $dataContr['hidePrev'] = $offset <= 0;
    $dataContr['hideNext'] = $offset >= (is_countable($logs) ? count($logs) : 0) - SvcLogRepository::PAGINATOR_PER_PAGE;
    $dataContr['count'] = is_countable($logs) ? count($logs) : 0;
    $dataContr['from'] = min($offset + 1, is_countable($logs) ? count($logs) : 0);
    $dataContr['to'] = min($offset + SvcLogRepository::PAGINATOR_PER_PAGE, is_countable($logs) ? count($logs) : 0);

    return $this->render('@SvcLog/log_viewer/_table_rows.html.twig', [
      'logs' => $logs,
      'dataContr' => $dataContr,
      'hideSourceCols' => $hideSourceCols,
    ]);
  }

  public function viewDetail(int $id, SvcLogRepository $svcLogRep): Response
  {
    $log = $svcLogRep->find($id);
    // if ($log->getSourceType() >= 90000) { // internal handled sourceType
    //   $log->setSourceTypeText(AppConstants::getSourceTypeText($log->getSourceType()));
    //   $log->setSourceIDText((string) $log->getSourceID());
    // } else {
    $log->setSourceTypeText($this->dataProvider->getSourceTypeText($log->getSourceType()));
    $log->setSourceIDText($this->dataProvider->getSourceIDText($log->getSourceID(), $log->getSourceType()));
    // }

    return $this->render('@SvcLog/log_viewer/_detail.html.twig', [
      'log' => $log,
      'enableUserSaving' => $this->enableUserSaving,
      'enableIPSaving' => $this->enableIPSaving,
    ]);
  }

  /**
   * check a (numeric) url parameter.
   */
  private function checkParam(?string $value): ?int
  {
    if ($value === null or $value === '') {
      return null;
    }

    return intval($value);
  }
}
