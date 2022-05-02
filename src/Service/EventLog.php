<?php

namespace Svc\LogBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use donatj\UserAgent\UserAgentParser;
use Exception;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Exception\IpSavingNotEnabledException;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\UtilBundle\Service\NetworkHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;


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

  public const ARR_LEVEL_TEXT = [
    self::LEVEL_ALL => 'all',
    self::LEVEL_DEBUG => 'debug',
    self::LEVEL_INFO => 'info',
    self::LEVEL_DATA => 'data',
    self::LEVEL_WARN => 'warn',
    self::LEVEL_ERROR => 'error',
    self::LEVEL_FATAL => 'fatal'
  ];

  public function __construct(
      private bool $enableSourceType,  /** @phpstan-ignore-line */
      private bool $enableIPSaving,
      private bool $enableUserSaving,
      private int $minLogLevel,
      private Security $security, 
      private EntityManagerInterface $entityManager,
      private SvcLogRepository $logRepo
  )
  {
  }

  /**
   * write a log record
   *
   * @param integer $sourceID the ID of the source object
   * @param integer|null $sourceType the type of the source (entityA = 1, entityB = 2, ...) - These types must be managed by yourself, best is to set constants in the application
   * @param array|null $options
   *  - int level
   *  - string message
   * @return boolean true if successfull
   */
  public function log(int $sourceID, ?int $sourceType = 0, ?array $options = []): bool
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $options = $resolver->resolve($options);

    if ($options['level'] < $this->minLogLevel) {
      return true;
    }

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

    if ($this->enableIPSaving) {
      $log->setIp(NetworkHelper::getIP());
    }

    try {
      $parser = new UserAgentParser();
      $ua = $parser();
      $log->setPlatform($ua->platform());
      $log->setBrowser($ua->browser());
      $log->setBrowserVersion($ua->browserVersion());
      $log->setReferer(NetworkHelper::getReferer());
    } catch (Exception) {
      $log->setUserAgent(NetworkHelper::getUserAgent()); // write current user agent without parse
    }

    if ($this->enableUserSaving) {
      try {
        $user = $this->security->getUser();  
        if ($user) {
          $log->setUserID($user->getId()); /** @phpstan-ignore-line */  

          if (method_exists($this->security->getUser(), 'getUserIdentifier')) { 
            $log->setUserName($user->getUserIdentifier());
          } else {
            $log->setUserName($user->getUserName()); /** @phpstan-ignore-line */
          }
        }
      } catch (Exception) {
        // ignore user record
      }
    }

    try {
      $this->entityManager->persist($log);
      $this->entityManager->flush();
    } catch (Exception) {
      return false;
    }

    return true;
  }

  /**
   * configure options for log entries
   *
   * @param OptionsResolver $resolver
   * @return void
   */
  private function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'level'    => self::LEVEL_DATA,
      'message'  => null,
    ]);

    $resolver->setAllowedTypes('level', ['int', 'null']);
    $resolver->setAllowedTypes('message', ['string', 'null']);
  }

  /**
   * fill the location info, called in batch because timing
   *
   * @return integer count of successful locations set
   * @throws LogExceptionInterface
   */
  public function batchFillLocation(): int
  {
    if (!$this->enableIPSaving) {
      throw new IpSavingNotEnabledException();
    }

    $successCnt = 0;
    foreach ($this->logRepo->findBy(['country' => null]) as $entry) {
      if (!$entry->getIp()) {
        $entry->setCountry("-");
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
    }
    $this->entityManager->flush();
    return $successCnt;
  }

  public function purgeLogs(int $keepMonth, bool $dryRun): int {
    
    return 0;
  }
}
