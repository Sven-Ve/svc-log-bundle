<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Command;

use Svc\LogBundle\Service\StatsHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to create monthly statistics.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
#[AsCommand(
    name: 'svc_log:stat-aggregate',
    description: 'Create statistics.',
)]
final class StatMonthlyCommand extends Command
{
    use LockableTrait;

    public function __construct(private StatsHelper $statsHelper)
    {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(shortcut: 'f', description: 'Reload all statistics (otherwise only rebuild current data)')] bool $fresh = false,
    ): int {

        if (!$this->lock()) {
            $io->caution('The command is already running in another process.');

            return Command::FAILURE;
        }
        $io->title('Create monthly statistics');

        $res = $this->statsHelper->aggrMonthly($fresh);
        $io->success('Aggragation successfully runs. ' . $res['inserted'] . ' statistic records created.');

        $this->release();

        return Command::SUCCESS;
    }
}
