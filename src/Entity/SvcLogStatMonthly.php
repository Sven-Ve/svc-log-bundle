<?php

namespace Svc\LogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;

/** @phpstan-ignore-next-line */
#[ORM\Entity(repositoryClass: SvcLogStatMonthlyRepository::class)]
#[UniqueConstraint(columns: ['month', 'source_id', 'source_type', 'log_level'])]
class SvcLogStatMonthly
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: 'integer')]
  private $id;

  #[ORM\Column(type: 'string', length: 7)]
  private $month;

  #[ORM\Column(type: 'integer')]
  private $sourceID;

  #[ORM\Column(type: 'integer')]
  private int $sourceType = 0;

  #[ORM\Column(type: 'integer')]
  private $logLevel;

  #[ORM\Column(type: 'integer')]
  private $logCount;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getMonth(): ?string
  {
    return $this->month;
  }

  public function setMonth(string $month): self
  {
    $this->month = $month;

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

  public function getSourceType(): ?int
  {
    return $this->sourceType;
  }

  public function setSourceType(int $sourceType): self
  {
    $this->sourceType = $sourceType;

    return $this;
  }

  public function getLogLevel(): ?int
  {
    return $this->logLevel;
  }

  public function setLogLevel(int $logLevel): self
  {
    $this->logLevel = $logLevel;

    return $this;
  }

  public function getLogCount(): ?int
  {
    return $this->logCount;
  }

  public function setLogCount(int $logCount): self
  {
    $this->logCount = $logCount;

    return $this;
  }
}
