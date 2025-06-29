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
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
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

    public function __invoke(
        SymfonyStyle $io,
        #[Option(shortcut: 'd', description: 'Dry run - shows only the number of entries that would be deleted')] bool $dryRun = false,
        #[Option(description: 'Number of month to keep')] int $month = 6,
    ): int {
        if (!$this->lock()) {
            $io->caution('The command is already running in another process.');

            return Command::FAILURE;
        }



        if ($month < 1) {
            $io->error('Month must be greather or equal 1!');

            return Command::FAILURE;
        }

        $io->title('Purge old log events');
        $io->writeln('Keep Month:' . $month);
        $io->writeln('Dry run: ' . ($dryRun ? 'yes' : 'no'));

        $count = $this->purgeHelper->purgeLogs($month, $dryRun);

        $io->writeln($count . ' log records purged.' . ($dryRun ? ' (dryrun)' : ''));
        $io->success('Purge successfull.' . ($dryRun ? ' (dryrun)' : ''));

        $this->release();

        return Command::SUCCESS;
    }
}
