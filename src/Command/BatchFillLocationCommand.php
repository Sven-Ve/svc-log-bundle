<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Command;

use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Service\BatchHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * command to fill locations.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
#[AsCommand(
    name: 'svc_log:fill-location',
    description: 'Fill country and city (in batch because timing).',
    hidden: false
)]
class BatchFillLocationCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly BatchHelper $batchHelper,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(shortcut: 'f', description: 'Reload all empty countries')] bool $force = false,
    ): int {

        if (!$this->lock()) {
            $io->caution('The command is already running in another process.');

            return Command::FAILURE;
        }

        $io->title('Fill country and city for event logs');

        try {
            $successCnt = $this->batchHelper->batchFillLocation($force, $io);
        } catch (LogExceptionInterface $e) {
            $io->error($e->getReason());

            $this->release();

            return Command::FAILURE;
        }

        $io->success("$successCnt locations set");

        $this->release();

        return Command::SUCCESS;
    }
}
