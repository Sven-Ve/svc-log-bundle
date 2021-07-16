<?php

namespace Svc\LogBundle\Command;

use Exception;
use Svc\LogBundle\Exception\ExceptionInterface;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Service\EventLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BatchFillLocationCommand extends Command
{
  protected static $defaultName = 'svc_log:fill-location';
  protected static $defaultDescription = 'Fill country and city (in batch because timing)';

  private $eventLog;

  public function __construct(EventLog $eventLog)
  {
    parent::__construct();
    $this->eventLog = $eventLog;
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
    } catch (Exception $e) {
      $io->error($e->getMessage());
      return Command::FAILURE;
    }

    $io->success("$successCnt locations set");
    return Command::SUCCESS;
  }
}
