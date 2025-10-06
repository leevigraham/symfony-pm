<?php

namespace App\Entity;

use App\Enum\WorkItemPriority;
use App\Enum\WorkItemStatus;
use App\Repository\WorkItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkItemRepository::class)]
class WorkItem
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $key = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $sequence = null;

    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'workItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $reporter = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $assignee = null;

    /**
     * @var Collection<int, WorkLog>
     */
    #[ORM\OneToMany(targetEntity: WorkLog::class, mappedBy: 'workItem', orphanRemoval: true)]
    private Collection $workLogs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importKey = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(allowNull: false)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?int $originalEstimateInSeconds = null;

    #[ORM\Column(nullable: true)]
    private ?int $remainingEstimateInSeconds = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeSpentInSeconds = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childrenWorkItems')]
    private ?self $parentWorkItem = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentWorkItem')]
    private Collection $childrenWorkItems;

    #[ORM\Column(nullable: true, enumType: WorkItemPriority::class)]
    private ?WorkItemPriority $priority = null;

    #[ORM\Column(nullable: true, enumType: WorkItemStatus::class)]
    private ?WorkItemStatus $status = null;

    public function __construct()
    {
        $this->workLogs = new ArrayCollection();
        $this->childrenWorkItems = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    public function setAssignee(?User $assignee): static
    {
        $this->assignee = $assignee;

        return $this;
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
            $workLog->setWorkItem($this);
        }

        return $this;
    }

    public function removeWorkLog(WorkLog $workLog): static
    {
        if ($this->workLogs->removeElement($workLog)) {
            // set the owning side to null (unless already changed)
            if ($workLog->getWorkItem() === $this) {
                $workLog->setWorkItem(null);
            }
        }

        return $this;
    }

    public function getImportKey(): ?string
    {
        return $this->importKey;
    }

    public function setImportKey(?string $importKey): static
    {
        $this->importKey = $importKey;

        return $this;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): static
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getOriginalEstimateInSeconds(): ?int
    {
        return $this->originalEstimateInSeconds;
    }

    public function setOriginalEstimateInSeconds(?int $originalEstimateInSeconds): static
    {
        $this->originalEstimateInSeconds = $originalEstimateInSeconds;

        return $this;
    }

    public function getRemainingEstimateInSeconds(): ?int
    {
        return $this->remainingEstimateInSeconds;
    }

    public function setRemainingEstimateInSeconds(?int $remainingEstimateInSeconds): static
    {
        $this->remainingEstimateInSeconds = $remainingEstimateInSeconds;

        return $this;
    }

    public function getTimeSpentInSeconds(): ?int
    {
        return $this->timeSpentInSeconds;
    }

    public function setTimeSpentInSeconds(?int $timeSpentInSeconds): static
    {
        $this->timeSpentInSeconds = $timeSpentInSeconds;

        return $this;
    }

    public function getParentWorkItem(): ?self
    {
        return $this->parentWorkItem;
    }

    public function setParentWorkItem(?self $parentWorkItem): static
    {
        $this->parentWorkItem = $parentWorkItem;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildrenWorkItems(): Collection
    {
        return $this->childrenWorkItems;
    }

    public function addChildrenWorkItem(self $childrenWorkItem): static
    {
        if (!$this->childrenWorkItems->contains($childrenWorkItem)) {
            $this->childrenWorkItems->add($childrenWorkItem);
            $childrenWorkItem->setParentWorkItem($this);
        }

        return $this;
    }

    public function removeChildrenWorkItem(self $childrenWorkItem): static
    {
        if ($this->childrenWorkItems->removeElement($childrenWorkItem)) {
            // set the owning side to null (unless already changed)
            if ($childrenWorkItem->getParentWorkItem() === $this) {
                $childrenWorkItem->setParentWorkItem(null);
            }
        }

        return $this;
    }

    public function getPriority(): ?WorkItemPriority
    {
        return $this->priority;
    }

    public function setPriority(?WorkItemPriority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?WorkItemStatus
    {
        return $this->status;
    }

    public function setStatus(?WorkItemStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
