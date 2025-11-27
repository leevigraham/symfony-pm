<?php

namespace App\Entity;

use App\Enum\WorkLogStates;
use App\Repository\WorkLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Trait\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WorkLogRepository::class)]
class WorkLog
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column(enumType: WorkLogStates::class)]
    public ?WorkLogStates $state = WorkLogStates::STOPPED;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    public ?int $durationInSeconds = null;

    #[ORM\Column]
    public ?bool $billable = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $importKey = null;

    #[ORM\Column(nullable: true)]
    public ?int $billableDurationInSeconds = null;

    #[ORM\ManyToOne(inversedBy: 'workLogs')]
    #[ORM\JoinColumn(nullable: false)]
    public ?WorkItem $workItem = null {
        get => $this->workItem;
        set (?WorkItem $workItem) {
            $workItem?->addWorkLog($this);
            $this->workItem = $workItem;
        }
    }

    public function __toString(): string {
        return (string) $this->id;
    }
}

