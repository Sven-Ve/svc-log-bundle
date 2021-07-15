<?php

namespace Svc\LogBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use donatj\UserAgent\UserAgentParser;
use Exception;
use Svc\LogBundle\Entity\SvcLog;
use Svc\UtilBundle\Service\NetworkHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Helper class to log events
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class EventLog
{

  public const LEVEL_ALL = 0;
  public const LEVEL_DEBUG = 1;
  public const LEVEL_INFO = 2;
  /**
   * data is a special log level to store access data (page views, ...)
   */
  public const LEVEL_DATA = 3;
  public const LEVEL_WARN = 4;
  public const LEVEL_ERROR = 5;
  public const LEVEL_FATAL = 6;

  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function log(int $sourceID, ?int $sourceType = 0, ?array $options = []): bool
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $options = $resolver->resolve($options);

    $log = new SvcLog();
    $log->setLogDate(new DateTime());
    $log->setSourceID($sourceID);
    $log->setSourceType($sourceType);

    if ($options['message']) {
      $log->setMessage($options['message']);
    }
    if ($options['level']) {
      $log->setLogLevel($options['level']);
    }


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

  private function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'level'    => self::LEVEL_DATA,
      'message'  => null,
    ]);

    $resolver->setAllowedTypes('level', ['int', 'null']);
    $resolver->setAllowedTypes('message', ['string', 'null']);
  }
}
