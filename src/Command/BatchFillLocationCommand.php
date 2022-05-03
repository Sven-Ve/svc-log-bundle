<?php

namespace Svc\LogBundle\Command;

use Exception;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Service\EventLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * command to fill locations.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class BatchFillLocationCommand extends Command
{
  protected static $defaultName = 'svc_log:fill-location';
  protected static $defaultDescription = 'Fill country and city (in batch because timing)';

  public function __construct(private EventLog $eventLog)
  {
    parent::__construct();
  }

  protected function configure()
  {
    $this->setDescription(self::$defaultDescription);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    try {
      $successCnt = $this->eventLog->batchFillLocation();
    } catch (LogExceptionInterface $e) {
      $io->error($e->getReason());

      return Command::FAILURE;
    } catch (Exception $e) { /* @phpstan-ignore-line */
      $io->error($e->getMessage());

      return Command::FAILURE;
    }

    $io->success("$successCnt locations set");

    return Command::SUCCESS;
  }
}
