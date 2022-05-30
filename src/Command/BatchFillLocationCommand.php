<?php

namespace Svc\LogBundle\Command;

use Exception;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Service\EventLog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

  public function __construct(private EventLog $eventLog)
  {
    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Reload all empty countries');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    if (!$this->lock()) {
      $io->caution('The command is already running in another process.');

      return Command::FAILURE;
    }

    $io->title('Fill country and city for event logs');
    $force = $input->getOption('force');

    try {
      $successCnt = $this->eventLog->batchFillLocation($force, $io);
    } catch (LogExceptionInterface $e) {
      $io->error($e->getReason());

      $this->release();

      return Command::FAILURE;
    } catch (Exception $e) { /* @phpstan-ignore-line */
      $io->error($e->getMessage());

      $this->release();

      return Command::FAILURE;
    }

    $io->success("$successCnt locations set");

    $this->release();

    return Command::SUCCESS;
  }
}
