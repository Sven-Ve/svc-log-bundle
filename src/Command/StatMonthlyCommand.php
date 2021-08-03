<?php

namespace Svc\LogBundle\Command;

use Svc\LogBundle\Service\StatsHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to create monthly statistics
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class StatMonthlyCommand extends Command
{
  protected static $defaultName = 'svc_log:stat-aggregate';
  protected static $defaultDescription = 'Create statistics';

  private $statsHelper;
  public function __construct(StatsHelper $statsHelper)
  {
    parent::__construct();
    $this->statsHelper = $statsHelper;
  }
  protected function configure()
  {
    $this
        ->setDescription(self::$defaultDescription)
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
