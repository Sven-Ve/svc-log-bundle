<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Controller;

use Svc\LogBundle\Service\DailySummaryHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * show the daily summary.
     */
    public function view(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $raw = $request->query->getBoolean('raw');
        if (!$raw) {
            $summary = $this->dailySummary->getSummary();

            return $this->render('@SvcLog/daily_summary/show.html.twig', [
                'content' => $summary,
                'header' => 'Daily summary',
            ]);
        }

        return new Response($this->dailySummary->getSummary());
    }
}
