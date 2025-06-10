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

use Svc\LogBundle\Service\PurgeHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * purge old log files.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
#[AsCommand(
    name: 'svc_log:purge-logs',
    description: 'Purge old log events.',
    hidden: false
)]
class PurgeLogsCommand extends Command
{
    use LockableTrait;

    public function __construct(private PurgeHelper $purgeHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dryrun', 'd', InputOption::VALUE_NONE, 'Dry run - shows only the number of entries that would be deleted')
            ->addOption('month', null, InputOption::VALUE_OPTIONAL, 'Number of month to keep (default = 6)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->lock()) {
            $io->caution('The command is already running in another process.');

            return Command::FAILURE;
        }

        $io->title('Purge old log events');

        $dryRun = $input->getOption('dryrun');

        $month = $input->getOption('month');

        if ($month === null) {
            $month = 6;
        } elseif (!ctype_digit($month) or !is_numeric($month)) {
            $io->error('Month must be an integer!');

            return Command::FAILURE;
        } else {
            $month = intval($month);
        }

        if ($month < 1) {
            $io->error('Month must be greather or equal 1!');

            return Command::FAILURE;
        }
        $io->writeln('Keep Month:' . $month);

        $count = $this->purgeHelper->purgeLogs($month, $dryRun);

        $io->writeln($count . ' log records purged.' . ($dryRun ? ' (dryrun)' : ''));
        $io->success('Purge successfull.' . ($dryRun ? ' (dryrun)' : ''));

        $this->release();

        return Command::SUCCESS;
    }
}
