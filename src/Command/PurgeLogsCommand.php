<?php

namespace Svc\LogBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * purge old log files.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class PurgeLogsCommand extends Command
{
  protected static $defaultName = 'svc_log:purge-logs';

  protected static $defaultDescription = 'Purge old log files';

  protected function configure()
  {
    $this
        ->setDescription(self::$defaultDescription)
        ->addOption('dryrun', 'd', InputOption::VALUE_NONE, 'Dry run - shows only the number of entries that would be deleted')
        ->addOption('month', null, InputOption::VALUE_OPTIONAL, 'Number of month to keep', 6)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
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
    $io->info('Month:' . $month);

//    $io->info($res['inserted'] . ' statistic records created.');
    $io->success('Aggragation successfully runs.');

    return Command::SUCCESS;
  }
}
