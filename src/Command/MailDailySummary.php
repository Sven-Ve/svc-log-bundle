<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Command;

use Svc\LogBundle\Service\DailySummaryHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
class MailDailySummary extends Command
{
    use LockableTrait;

    public function __construct(private DailySummaryHelper $dailySummaryHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //   ->addOption('fresh', 'f', InputOption::VALUE_NONE, 'Reload all statistics (otherwise only rebuild current data)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

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
