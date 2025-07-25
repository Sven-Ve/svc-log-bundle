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
use Doctrine\Persistence\ManagerRegistry;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Enum\LogLevel;
use Svc\UtilBundle\Service\NetworkHelper;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Helper class to log events.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class EventLog
{
    public function __construct(
        private readonly bool $enableIPSaving,
        private readonly bool $enableUserSaving,
        private readonly LogLevel $minLogLevel,
        private readonly bool $enableLogger,
        private readonly LogLevel $loggerMinLogLevel,
        private readonly bool $disable404Logger,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerHelper $loggerHelper,
        private readonly ManagerRegistry $managerRegistry,
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
            } catch (\Exception) {
                // ignore errors here, because the user may not be available
            }
        }

        try {
            if (!$this->entityManager->isOpen()) {
                $this->managerRegistry->resetManager(); // reset the manager to ensure a new one is created
            }
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

        return !$vErrors;
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
