<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\LogAppConstants;

#[ORM\Entity(repositoryClass: SvcLogRepository::class)]
class SvcLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column()]
    private int $sourceType = 0;

    #[ORM\Column()]
    private ?int $sourceID = null;

    #[ORM\Column()]
    private \DateTime $logDate;

    #[ORM\Column()]
    private LogLevel $logLevel = LogLevel::DATA;

    #[ORM\Column(nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $platform = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $browser = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $browserVersion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $os = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $osVersion = null;

    #[ORM\Column(nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(nullable: true)]
    private ?string $userName = null;

    #[ORM\Column(nullable: true)]
    private ?int $userID = 0;

    #[ORM\Column(nullable: true)]
    private ?string $errorText = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $bot = false;

    #[ORM\Column(nullable: true)]
    private ?string $botName = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $mobile = false;

    /**
     * not in database, only helper columns.
     */
    private ?string $sourceIDText = null;

    private ?string $sourceTypeText = null;

    public function __construct()
    {
        $this->logDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceType(): ?int
    {
        return $this->sourceType;
    }

    public function setSourceType(int $sourceType): self
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    public function getSourceID(): ?int
    {
        return $this->sourceID;
    }

    public function setSourceID(int $sourceID): self
    {
        $this->sourceID = $sourceID;

        return $this;
    }

    public function getLogLevel(): LogLevel
    {
        return $this->logLevel;
    }

    public function getLogLevelText(): string
    {
        return $this->logLevel->label();
    }

    public function setLogLevel(LogLevel $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    public function getLogDate(): \DateTimeInterface
    {
        return $this->logDate;
    }

    public function setLogDate(\DateTimeInterface $logDate): self
    {
        $this->logDate = \DateTime::createFromInterface($logDate);

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(?string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function getBrowserVersion(): ?string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(?string $browserVersion): self
    {
        $this->browserVersion = $browserVersion;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }

    public function setOsVersion(?string $osVersion): self
    {
        $this->osVersion = $osVersion;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;

        return $this;
    }

    public function getSourceIDText(): ?string
    {
        return $this->sourceIDText;
    }

    public function setSourceIDText(?string $sourceIDText): self
    {
        $this->sourceIDText = $sourceIDText;

        return $this;
    }

    public function getSourceTypeText(): ?string
    {
        if ($this->sourceType >= LogAppConstants::LOG_TYPE_INTERNAL_MIN) {
            return LogAppConstants::getSourceTypeText($this->sourceType);
        }

        return $this->sourceTypeText;
    }

    public function setSourceTypeText(?string $sourceTypeText): self
    {
        $this->sourceTypeText = $sourceTypeText;

        return $this;
    }

    public function getUserID(): ?int
    {
        return $this->userID;
    }

    public function setUserID(?int $userID): self
    {
        $this->userID = $userID;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getErrorText(): ?string
    {
        return $this->errorText;
    }

    public function setErrorText(?string $errorText): self
    {
        $this->errorText = $errorText;

        return $this;
    }

    public function getBotName(): ?string
    {
        return $this->botName;
    }

    public function setBotName(?string $botName): self
    {
        $this->botName = $botName;

        return $this;
    }

    public function isBot(): bool
    {
        return $this->bot;
    }

    public function setBot(bool $bot): static
    {
        $this->bot = $bot;

        return $this;
    }

    public function isMobile(): bool
    {
        return $this->mobile;
    }

    public function setMobile(bool $mobile): static
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * get the background color value per log level (use the background values from bootstrap 5).
     */
    public function getLogLevelBGColor(): string
    {
        return match ($this->logLevel) {
            LogLevel::INFO => 'primary',
            LogLevel::DATA => 'success',
            LogLevel::WARN => 'warning',
            LogLevel::ERROR => 'danger',
            LogLevel::CRITICAL => 'danger',
            LogLevel::ALERT => 'danger',
            LogLevel::EMERGENCY => 'danger',
            default => 'secondary',
        };
    }

    /**
     * get the background color value per log level (use html values).
     */
    public function getLogLevelBGColorHTML(): string
    {
        return match ($this->logLevel) {
            LogLevel::INFO => '',
            LogLevel::DATA => 'green',
            LogLevel::WARN => 'yellow',
            LogLevel::ERROR => 'red',
            LogLevel::CRITICAL => 'red',
            LogLevel::ALERT => '#880808',
            LogLevel::EMERGENCY => '#880808',
            default => 'gray',
        };
    }

    /**
     * get the foreground color value per log level (use the background values from bootstrap 5).
     */
    public function getLogLevelFGColor(): string
    {
        return match ($this->logLevel) {
            LogLevel::WARN => 'dark',
            default => 'white',
        };
    }

    /**
     * get the foreground color value per log level (use html values).
     */
    public function getLogLevelFGColorHTML(): string
    {
        return match ($this->logLevel) {
            LogLevel::WARN => 'black',
            LogLevel::INFO => 'black',
            default => 'white',
        };
    }

    /**
     * get the complete bootstrap 5 class for the logLevel.
     */
    public function getLogLevelBootstrap5Class(): string
    {
        return 'bg-' . $this->getLogLevelBGColor() . ' text-' . $this->getLogLevelFGColor();
    }
}
