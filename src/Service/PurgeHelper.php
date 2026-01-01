<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Service;

use Svc\LogBundle\Repository\SvcLogRepository;

/**
 * Helper class for purging log records.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class PurgeHelper
{
    public function __construct(private readonly SvcLogRepository $svcLogRep)
    {
    }

    /**
     * aggregate data for monthly statistics.
     */
    public function purgeLogs(int $purgeMonth, bool $dryRun): int
    {
        $today = new \DateTime();
        $firstDay = new \DateTime($today->format('Y-m-01'));

        $subMonth = new \DateInterval('P' . (string) $purgeMonth . 'M');

        $firstDayToKeep = $firstDay->sub($subMonth);

        return $this->svcLogRep->purgeOldData($firstDayToKeep, $dryRun);
    }
}
