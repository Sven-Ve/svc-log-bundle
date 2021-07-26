<?php

namespace Svc\LogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Svc\LogBundle\Repository\SvcLogRepository;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class LogViewer extends AbstractController
{


  /**
   * show statistics for a video
   */
  public function view(Request $request, SvcLogRepository $svcLogRep): Response
  {

    $logs = $svcLogRep->findBy([],['id' => 'desc']);
    return $this->render('@SvcLog/log_viewer/viewer.html.twig', [
      'logs' => $logs
    ]);
  }

  
}
