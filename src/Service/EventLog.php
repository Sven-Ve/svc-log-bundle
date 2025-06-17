<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Service;

use DeviceDetector\DeviceDetector;
use Doctrine\ORM\EntityManagerInterface;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\UtilBundle\Service\NetworkHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Helper class to log events.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class EventLog
{
    /*
    public const LEVEL_ALL = 0;
    public const LEVEL_DEBUG = 1;
    public const LEVEL_INFO = 2;
    public const LEVEL_DATA = 3; // data is a special log level to store access data (page views, ...).
    public const LEVEL_WARN = 4;
    public const LEVEL_ERROR = 5;
    public const LEVEL_FATAL = 6;
    public const LEVEL_CRITICAL = 6; // same as FATAL
    public const LEVEL_ALERT = 7;
    public const LEVEL_EMERGENCY = 8;

    public const ARR_LEVEL_TEXT = [
      self::LEVEL_ALL => 'all',
      self::LEVEL_DEBUG => 'debug',
      self::LEVEL_INFO => 'info',
      self::LEVEL_DATA => 'data',
      self::LEVEL_WARN => 'warn',
      self::LEVEL_ERROR => 'error',
      self::LEVEL_FATAL => 'fatal',
      self::LEVEL_ALERT => 'alert',
      self::LEVEL_EMERGENCY => 'emergency',
    ];

    */

    public function __construct(
        private readonly bool $enableIPSaving,
        private readonly bool $enableUserSaving,
        private readonly LogLevel $minLogLevel,
        private readonly bool $enableLogger,
        private readonly LogLevel $loggerMinLogLevel,
        private readonly bool $disable404Logger,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly SvcLogRepository $logRepo,
        private readonly LoggerHelper $loggerHelper,
    ) {
    }

    /**
     * write a log record.
     *
     * @param int      $sourceID   the ID of the source object
     * @param int|null $sourceType the type of the source (entityA = 1, entityB = 2, ...) - These types must be managed by yourself, best is to set constants in the application
     *
     * @return bool true if successfully
     */
    public function writeLog(
        int $sourceID,
        ?int $sourceType = 0,
        LogLevel $level = LogLevel::DATA,
        ?string $message = null,
        ?string $errorText = null,
        ?int $httpStatusCode = null,
    ): bool {
        $vErrors = false;

        if ($level->value < $this->minLogLevel->value) {
            return true;
        }

        $log = new SvcLog();
        $log->setSourceID($sourceID);
        $log->setSourceType($sourceType);
        $log->setMessage($message !== null ? mb_substr($message, 0, 254) : null);
        $log->setLogLevel($level);
        $log->setErrorText($errorText !== null ? mb_substr($errorText, 0, 254) : null);

        if ($this->enableIPSaving) {
            $log->setIp(NetworkHelper::getIP());
        }

        try {
            $userAgent = NetworkHelper::getUserAgent();
            if ($userAgent) {
                $devDetector = new DeviceDetector($userAgent);
                $devDetector->parse();
                $log->setPlatform($devDetector->getBrandName());
                $log->setBrowser($devDetector->getClient('name'));  /* @phpstan-ignore-line */
                $log->setBrowserVersion($devDetector->getClient('version'));  /* @phpstan-ignore-line */

                $log->setOs($devDetector->getOs('name'));  /* @phpstan-ignore-line */
                $log->setOsVersion($devDetector->getOs('version'));  /* @phpstan-ignore-line */
                $log->setMobile($devDetector->isMobile());

                if ($devDetector->isBot()) {
                    $log->setBot(true);
                    $log->setBotName($devDetector->getBot()['name']);  /* @phpstan-ignore-line */
                }
            }

            $log->setReferer(NetworkHelper::getReferer());
        } catch (\Exception) {
            $log->setUserAgent(NetworkHelper::getUserAgent()); // write current user agent without parse
        }

        if ($this->enableUserSaving) {
            try {
                $user = $this->security->getUser();
                if ($user) {
                    $log->setUserID($user->getId()); /* @phpstan-ignore-line */

                    if (method_exists($this->security->getUser(), 'getUserIdentifier')) {
                        $log->setUserName($user->getUserIdentifier());
                    } else {
                        $log->setUserName($user->getUserName()); /* @phpstan-ignore-line */
                    }
                }
            } catch (\Exception $e) {
                dump($e->getMessage());
            }
        }

        try {
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (\Exception) {
            $vErrors = true;
        }

        if (
            $this->enableLogger
            and $this->loggerMinLogLevel->value <= $level->value
            and $level != LogLevel::DATA
            and (!$this->disable404Logger or ($httpStatusCode ?? 0) != 404)
        ) {
            if (!$this->loggerHelper->send($log)) {
                $vErrors = true;
            }
        }

        return $vErrors;
    }

    /*
    #[\Deprecated('use writeLog instead', '1.8')]
    public function log(int $sourceID, ?int $sourceType = 0, ?array $options = []): bool
    {
      $resolver = new OptionsResolver();
      $this->configureOptions($resolver);
      $options = $resolver->resolve($options);

      return $this->writeLog($sourceID, $sourceType,
        level: LogLevel::getLogLevelfromInt($options['level']),
        message: $options['message'],
        errorText: $options['errorText'],
        httpStatusCode: $options['httpStatusCode']
      );
    }


    private function configureOptions(OptionsResolver $resolver): void
    {
      $resolver->setDefaults([
        'level' => self::LEVEL_DATA,
        'message' => null,
        'errorText' => null,
        'httpStatusCode' => null,
      ]);

      $resolver->setAllowedTypes('level', ['int', 'null']);
      $resolver->setAllowedTypes('message', ['string', 'null']);
      $resolver->setAllowedTypes('errorText', ['string', 'null']);
      $resolver->setAllowedTypes('httpStatusCode', ['int', 'null']);
    }
    */

    /**
     * fill the location info, called in batch because timing.
     *
     * @throws LogExceptionInterface
     *
     * @return int count of successful locations set
     */
    public function batchFillLocation(bool $force, SymfonyStyle $io): int
    {
        if (!$this->enableIPSaving) {
            throw new DeleteAllLogsForbidden();
        }

        $successCnt = 0;
        $counter = 0;
        if ($force) {
            $entries = $this->logRepo->findBy(['country' => '-']);
        } else {
            $entries = $this->logRepo->findBy(['country' => null]);
        }

        if (count($entries) == 0) {
            return 0;
        }

        $progressBar = new ProgressBar($io, count($entries));
        $progressBar->start();

        foreach ($entries as $entry) {
            $progressBar->advance();

            try {
                if (!$entry->getIp() or $entry->getIp() == '127.0.0.1') {
                    $entry->setCountry('-');
                    continue;
                }

                ++$counter;
                if ($counter == 100) {
                    $io->writeln(' Sleep 70 seconds because api limit on http://www.geoplugin.net (' . $successCnt . ' countries found).');
                    $this->entityManager->flush();
                    sleep(70);
                    $counter = 0;
                }

                $location = NetworkHelper::getLocationInfoByIp($entry->getIp());
                if ($location['country']) {
                    $entry->setCountry($location['country']);
                    $entry->setCity($location['city']);
                    ++$successCnt;
                } else {
                    $entry->setCountry('-');
                }
            } catch (\Exception) {
                $entry->setCountry('-');
            }
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('Cannot save data: ' . $e->getMessage());
        }

        $progressBar->finish();

        return $successCnt;
    }

    /**
     * @return array<mixed>
     */
    public static function getLevelsForChoices(bool $includeAll = false): array
    {
        $choices = [];
        if ($includeAll) {
            $choices[0] = 'all';
        }

        foreach (LogLevel::cases() as $level) {
            $choices[$level->value] = $level->label();
        }

        return $choices;
    }
}
