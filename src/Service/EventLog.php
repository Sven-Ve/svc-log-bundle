<?php

namespace Svc\LogBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use donatj\UserAgent\UserAgentParser;
use Exception;
use Svc\LogBundle\Entity\SvcLog;
use Svc\UtilBundle\Service\NetworkHelper;

/**
 * Helper class to log events
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class EventLog
{

  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function log(string $message, int $sourceID, ?int $sourceType = 0): bool
  {
    $log = new SvcLog();
    $log->setLogDate(new DateTime());
    $log->setSourceID($sourceID);
    $log->setSourceType($sourceType);

    $log->setIp(NetworkHelper::getIP());

    try {
      $parser = new UserAgentParser();
      $ua = $parser();
      $log->setPlatform($ua->platform());
      $log->setBrowser($ua->browser());
      $log->setBrowserVersion($ua->browserVersion());
      $log->setReferer(NetworkHelper::getReferer());
    } catch (Exception $e) {
      $log->setUserAgent(NetworkHelper::getUserAgent()); // write current user agent without parse
    }

    $this->entityManager->persist($log);
    $this->entityManager->flush();

    return true;
  }
}
