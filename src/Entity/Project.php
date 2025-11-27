<?php

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Entity\Trait\TimestampableEntity;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(name: '`key`', type: 'string', length: 255, nullable: false)]
    public ?string $key = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    public ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $importKey = null;

    /**
     * @var Collection<int, WorkItem>
     */
    #[ORM\OneToMany(
        targetEntity: WorkItem::class,
        mappedBy: 'project',
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(["sequence" => "DESC"])]
    public Collection $workItems;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: true)]
    public ?Organisation $organisation = null {
        get => $this->organisation;
        set (?Organisation $organisation) {
            $organisation?->addProject($this);
            $this->organisation = $organisation;
        }
    }

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $lead = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $defaultAssignee = null;

    public function __construct()
    {
        $this->workItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: $this->id ?: '';
    }

    public function addWorkItem(WorkItem $workItem): static
    {
        if (!$this->workItems->contains($workItem)) {
            $this->workItems->add($workItem);
            $workItem->project = $this;
        }

        return $this;
    }

    public function removeWorkItem(WorkItem $workItem): static
    {
        if ($this->workItems->removeElement($workItem)) {
            // set the owning side to null (unless already changed)
            if ($workItem->project === $this) {
                $workItem->project = null;
            }
        }

        return $this;
    }
}
