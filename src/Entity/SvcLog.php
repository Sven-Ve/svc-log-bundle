<?php

namespace Svc\LogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\LogBundle\Service\EventLog;

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
  private int $logLevel = EventLog::LEVEL_DATA;

  #[ORM\Column(nullable: true)]
  private ?string $message = null;

  #[ORM\Column(length: 100, nullable: true)]
  private ?string  $ip = null;

  #[ORM\Column(nullable: true)]
  private ?string  $userAgent = null;

  #[ORM\Column(length: 50, nullable: true)]
  private ?string  $country = null;

  #[ORM\Column(length: 50, nullable: true)]
  private ?string  $city = null;

  #[ORM\Column(length: 50, nullable: true)]
  private ?string  $platform = null;

  #[ORM\Column(length: 50, nullable: true)]
  private ?string  $browser = null;

  #[ORM\Column(length: 50, nullable: true)]
  private ?string  $browserVersion = null;

  #[ORM\Column(nullable: true)]
  private ?string  $referer = null;

  #[ORM\Column(nullable: true)]
  private ?string  $userName = null;

  #[ORM\Column(nullable: true)]
  private ?int $userID = 0;

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

  public function getLogLevel(): ?int
  {
    return $this->logLevel;
  }

  public function getLogLevelText(): string
  {
    if ($this->logLevel == null) {
      return '?';
    }

    if (array_key_exists($this->logLevel, EventLog::ARR_LEVEL_TEXT)) {
      return EventLog::ARR_LEVEL_TEXT[$this->logLevel];
    } else {
      return '? (' . strval($this->logLevel) . ')';
    }
  }

  public function setLogLevel(int $logLevel): self
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
    $this->logDate = $logDate;

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

  /**
   * get the background color value per log level (use the background values from bootstrap 5).
   */
  public function getLogLevelBGColor(): string
  {
    return match ($this->logLevel) {
      EventLog::LEVEL_INFO => 'primary',
      EventLog::LEVEL_DATA => 'success',
      EventLog::LEVEL_WARN => 'warning',
      EventLog::LEVEL_ERROR => 'danger',
      EventLog::LEVEL_FATAL => 'danger',
      default => 'secondary',
    };
  }

  /**
   * get the foreground color value per log level (use the background values from bootstrap 5).
   */
  public function getLogLevelFGColor(): string
  {
    return match ($this->logLevel) {
      EventLog::LEVEL_WARN => 'dark',
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
