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

namespace Svc\LogBundle\Command;

use Svc\LogBundle\Service\DailySummaryHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to create monthly statistics.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
#[AsCommand(
    name: 'svc_log:daily-summary:mail',
    description: 'Mail daily log summary',
    hidden: false
)]
final class MailDailySummary extends Command
{
    use LockableTrait;

    public function __construct(private DailySummaryHelper $dailySummaryHelper)
    {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
    ): int {

        if (!$this->lock()) {
            $io->caution('The command is already running in another process.');

            return Command::FAILURE;
        }

        try {
            $res = $this->dailySummaryHelper->mailSummary();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $res = false;
        }
        $this->release();

        if ($res) {
            $io->success('Daily summary created successfully.');

            return Command::SUCCESS;
        }
        $io->error('Error during creating of Daily summary mail.');

        return Command::FAILURE;
    }
}
