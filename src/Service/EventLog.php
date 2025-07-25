<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
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
    private const MAX_MESSAGE_LENGTH = 254;
    private const MAX_ERROR_TEXT_LENGTH = 254;
    private const MAX_IP_LENGTH = 100;
    private const MAX_USER_AGENT_LENGTH = 500;
    private const MAX_URL_LENGTH = 500;
    private const MAX_STRING_LENGTH = 50;

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
        $log->setMessage($message !== null ? mb_substr($message, 0, self::MAX_MESSAGE_LENGTH) : null);
        $log->setLogLevel($level);
        $log->setErrorText($errorText !== null ? mb_substr($errorText, 0, self::MAX_ERROR_TEXT_LENGTH) : null);

        if ($this->enableIPSaving) {
            $rawIp = NetworkHelper::getIP();
            $sanitizedIp = $this->sanitizeIpAddress($rawIp);
            $log->setIp($sanitizedIp);
        }

        try {
            $userAgent = NetworkHelper::getUserAgent();
            if ($userAgent) {
                $sanitizedUserAgent = $this->sanitizeUserAgent($userAgent);
                $devDetector = new DeviceDetector($sanitizedUserAgent);
                $devDetector->parse();
                $log->setPlatform($this->sanitizeString($devDetector->getBrandName()));
                $log->setBrowser($this->sanitizeString($devDetector->getClient('name')));  /* @phpstan-ignore-line */
                $log->setBrowserVersion($this->sanitizeString($devDetector->getClient('version')));  /* @phpstan-ignore-line */

                $log->setOs($this->sanitizeString($devDetector->getOs('name')));  /* @phpstan-ignore-line */
                $log->setOsVersion($this->sanitizeString($devDetector->getOs('version')));  /* @phpstan-ignore-line */
                $log->setMobile($devDetector->isMobile());

                if ($devDetector->isBot()) {
                    $log->setBot(true);
                    $log->setBotName($this->sanitizeString($devDetector->getBot()['name']));  /* @phpstan-ignore-line */
                }
            }

            $referer = NetworkHelper::getReferer();
            $log->setReferer($this->sanitizeUrl($referer));
        } catch (\Exception) {
            $userAgent = NetworkHelper::getUserAgent();
            $log->setUserAgent($this->sanitizeUserAgent($userAgent)); // write current user agent without parse
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

    /**
     * Sanitize IP address to prevent injection attacks.
     */
    private function sanitizeIpAddress(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        // Validate IPv4 or IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return mb_substr($ip, 0, self::MAX_IP_LENGTH);
        }

        return null; // Invalid IP
    }

    /**
     * Sanitize User-Agent string.
     */
    private function sanitizeUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        // Remove potential script tags and limit length
        $sanitized = strip_tags($userAgent);
        $sanitized = preg_replace('/[<>"\']/', '', $sanitized);

        return mb_substr($sanitized, 0, self::MAX_USER_AGENT_LENGTH);
    }

    /**
     * Sanitize URL (referer).
     */
    private function sanitizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // Basic URL validation and sanitization
        $sanitized = filter_var($url, FILTER_SANITIZE_URL);
        if ($sanitized && (str_starts_with($sanitized, 'http://') || str_starts_with($sanitized, 'https://'))) {
            return mb_substr($sanitized, 0, self::MAX_URL_LENGTH);
        }

        return null;
    }

    /**
     * Sanitize generic string fields.
     */
    private function sanitizeString(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        // Remove HTML tags and limit length
        $sanitized = strip_tags($value);
        $sanitized = preg_replace('/[<>"\']/', '', $sanitized);

        return mb_substr($sanitized, 0, self::MAX_STRING_LENGTH);
    }
}
