<?php

namespace Svc\LogBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\UtilBundle\Service\NetworkHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BatchFillLocationCommand extends Command
{
  protected static $defaultName = 'svc_log:fill-location';
  protected static $defaultDescription = 'Fill country and city (in batch because timing)';

  private $logRepo;
  private $entityManager;

  public function __construct(SvcLogRepository $logRepo, EntityManagerInterface $entityManager)
  {
    parent::__construct();
    $this->logRepo = $logRepo;
    $this->entityManager = $entityManager;
  }

  protected function configure()
  {
    $this->setDescription(self::$defaultDescription);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $successCnt = 0;

    foreach ($this->logRepo->findBy(['country' => null]) as $entry) {
      if (!$entry->getIp()) {
        continue;
      }

      $location = NetworkHelper::getLocationInfoByIp($entry->getIp());
      if ($location['country']) {
        $entry->setCountry($location['country']);
        $entry->setCity($location['city']);
        $successCnt++;
      } else {
        $entry->setCountry("-");
      }

      $this->entityManager->flush();
    }

    $io->success("$successCnt locations set");
    return Command::SUCCESS;
  }
}
