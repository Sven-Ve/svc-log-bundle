<?php

declare(strict_types=1);

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
use Doctrine\ORM\Mapping\UniqueConstraint;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Repository\SvcLogStatMonthlyRepository;

#[ORM\Entity(repositoryClass: SvcLogStatMonthlyRepository::class)]
#[UniqueConstraint(columns: ['month', 'source_id', 'source_type', 'log_level'])]
class SvcLogStatMonthly
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 7)]
    private ?string $month = null;

    #[ORM\Column()]
    private ?int $sourceID = null;

    #[ORM\Column()]
    private int $sourceType = 0;

    #[ORM\Column()]
    private ?LogLevel $logLevel = null;

    #[ORM\Column()]
    private ?int $logCount = null;

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

    public function getLogLevel(): ?LogLevel
    {
        return $this->logLevel;
    }

    public function setLogLevel(LogLevel $logLevel): self
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
