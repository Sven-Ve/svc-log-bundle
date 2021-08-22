<?php

namespace Svc\LogBundle\Entity;

use Svc\LogBundle\Repository\SvcLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Svc\LogBundle\Service\EventLog;

/**
 * @ORM\Entity(repositoryClass=SvcLogRepository::class)
 */
class SvcLog
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="integer")
   */
  private $sourceType = 0;

  /**
   * @ORM\Column(type="integer")
   */
  private $sourceID;

  /**
   * @ORM\Column(type="datetime")
   */
  private $logDate;

  /**
   * @ORM\Column(type="integer")
   */
  private $logLevel = EventLog::LEVEL_DATA;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $message;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $ip;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $userAgent;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $country;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $city;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $platform;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $browser;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $browserVersion;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $referer;

  /**
   * not in database, only helper columns
   */
  private string|null $sourceIDText;
  private string|null $sourceTypeText;

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
    if ($this->logLevel === null) {
      return "?";
    }

    if (array_key_exists($this->logLevel, EventLog::ARR_LEVEL_TEXT)) {
      return EventLog::ARR_LEVEL_TEXT[$this->logLevel];
    } else {
      return "?";
    }
  }

  public function setLogLevel(int $logLevel): self
  {
    $this->logLevel = $logLevel;

    return $this;
  }

  public function getLogDate(): ?\DateTimeInterface
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
}
