<?php

namespace Svc\LogBundle\Command;

use Svc\LogBundle\Service\StatsHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to create monthly statistics.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
#[AsCommand(
  name: 'svc_log:stat-aggregate',
  description: 'Create statistics.',
  hidden: false
)]class StatMonthlyCommand extends Command
{
  public function __construct(private StatsHelper $statsHelper)
  {
    parent::__construct();
  }

  protected function configure()
  {
    $this
        ->addOption('fresh', 'f', InputOption::VALUE_NONE, 'Reload all statistics (otherwise only rebuild current data)')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $fresh = $input->getOption('fresh');

    $res = $this->statsHelper->aggrMonthly($fresh);
    $io->info($res['inserted'] . ' statistic records created.');
    $io->success('Aggragation successfully runs.');

    return Command::SUCCESS;
  }
}
