<?php

namespace App\Entity;

use App\Enum\WorkItemPriority;
use App\Enum\WorkItemStatus;
use App\Repository\WorkItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\BlameableEntity;
use App\Entity\Trait\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkItemRepository::class)]
class WorkItem
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $key = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(allowNull: false)]
    public ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column]
    public ?int $sequence = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $importKey = null;

    #[ORM\Column(nullable: true)]
    public ?int $originalEstimateInSeconds = null;

    #[ORM\Column(nullable: true)]
    public ?int $remainingEstimateInSeconds = null;

    #[ORM\Column(nullable: true)]
    public ?int $timeSpentInSeconds = null;

    #[ORM\Column(nullable: true, enumType: WorkItemPriority::class)]
    public ?WorkItemPriority $priority = null;

    #[ORM\Column(nullable: true, enumType: WorkItemStatus::class)]
    public ?WorkItemStatus $status = null;

    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'workItems')]
    #[ORM\JoinColumn(nullable: false)]
    public ?Project $project {
        get => $this->project;
        set (?Project $project) {
            $project?->addWorkItem($this);
            $this->project = $project;
        }
    }

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $reporter = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $assignee = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childrenWorkItems')]
    public ?self $parentWorkItem = null {
        get => $this->parentWorkItem;
        set (?self $parentWorkItem) {
            $parentWorkItem?->addChildrenWorkItem($this);
            $this->parentWorkItem = $parentWorkItem;
        }
    }

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentWorkItem')]
    public Collection $childrenWorkItems;

    /**
     * @var Collection<int, WorkLog>
     */
    #[ORM\OneToMany(targetEntity: WorkLog::class, mappedBy: 'workItem', orphanRemoval: true)]
    public Collection $workLogs;


    public function __construct()
    {
        $this->workLogs = new ArrayCollection();
        $this->childrenWorkItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @return Collection<int, WorkLog>
     */
    public function getWorkLogs(): Collection
    {
        return $this->workLogs;
    }

    public function addWorkLog(WorkLog $workLog): static
    {
        if (!$this->workLogs->contains($workLog)) {
            $this->workLogs->add($workLog);
            $workLog->workItem = $this;
        }

        return $this;
    }

    public function removeWorkLog(WorkLog $workLog): static
    {
        if ($this->workLogs->removeElement($workLog)) {
            // set the owning side to null (unless already changed)
            if ($workLog->workItem === $this) {
                $workLog->workItem = null;
            }
        }

        return $this;
    }

    public function addChildrenWorkItem(self $childrenWorkItem): static
    {
        if (!$this->childrenWorkItems->contains($childrenWorkItem)) {
            $this->childrenWorkItems->add($childrenWorkItem);
            $childrenWorkItem->parentWorkItem = $this;
        }

        return $this;
    }

    public function removeChildrenWorkItem(self $childrenWorkItem): static
    {
        if ($this->childrenWorkItems->removeElement($childrenWorkItem)) {
            // set the owning side to null (unless already changed)
            if ($childrenWorkItem->parentWorkItem === $this) {
                $childrenWorkItem->parentWorkItem = null;
            }
        }

        return $this;
    }
}
