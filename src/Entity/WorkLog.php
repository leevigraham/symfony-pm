<?php

namespace App\Entity;

use App\Enum\WorkLogStates;
use App\Repository\WorkLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WorkLogRepository::class)]
class WorkLog
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'workLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkItem $workItem = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(enumType: WorkLogStates::class)]
    private ?WorkLogStates $state = WorkLogStates::STOPPED;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationInSeconds = null;

    #[ORM\Column]
    private ?bool $billable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importKey = null;

    #[ORM\Column(nullable: true)]
    private ?int $billableDurationInSeconds = null;

    public function getImportKey(): ?string
    {
        return $this->importKey;
    }

    public function setImportKey(?string $importKey): static
    {
        $this->importKey = $importKey;

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getWorkItem(): ?WorkItem
    {
        return $this->workItem;
    }

    public function setWorkItem(?WorkItem $workItem): static
    {
        $this->workItem = $workItem;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): ?WorkLogStates
    {
        return $this->state;
    }

    public function setState(WorkLogStates $state, array $context = []): static
    {
        $this->state = $state;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getDurationInSeconds(): ?int
    {
        return $this->durationInSeconds;
    }

    public function setDurationInSeconds(?int $durationInSeconds): static
    {
        $this->durationInSeconds = $durationInSeconds;

        return $this;
    }

    public function isBillable(): ?bool
    {
        return $this->billable;
    }

    public function setBillable(bool $billable): static
    {
        $this->billable = $billable;

        return $this;
    }

    public function getBillableDurationInSeconds(): ?int
    {
        return $this->billableDurationInSeconds;
    }

    public function setBillableDurationInSeconds(?int $billableDurationInSeconds): static
    {
        $this->billableDurationInSeconds = $billableDurationInSeconds;

        return $this;
    }
}

