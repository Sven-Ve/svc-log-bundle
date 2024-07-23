<?php

namespace Svc\LogBundle\Controller;

use Svc\LogBundle\Service\DailySummaryHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for displaying the daily summary.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class DailySummaryController extends AbstractController
{
  public function __construct(private readonly DailySummaryHelper $dailySummary)
  {
  }

  /**
   * show a log table (without data, only the construct).
   */
  public function view(): Response
  {
    return new Response($this->dailySummary->getSummary());
  }
}
