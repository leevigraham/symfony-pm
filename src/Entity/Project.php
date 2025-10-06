<?php

namespace App\Entity;

use App\Entity\Interface\SluggableInterface;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements SluggableInterface
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: '`key`', type: 'string', length: 255, nullable: false)]
    private ?string $key = null;

    /**
     * @var Collection<int, WorkItem>
     */
    #[ORM\OneToMany(
        targetEntity: WorkItem::class,
        mappedBy: 'project',
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(["sequence" => "DESC"])]
    private Collection $workItems;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Organisation $organisation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $lead = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $defaultAssignee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importKey = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->workItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: $this->id ?: '';
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return Collection<int, WorkItem>
     */
    public function getWorkItems(): Collection
    {
        return $this->workItems;
    }

    public function addWorkItem(WorkItem $workItem): static
    {
        if (!$this->workItems->contains($workItem)) {
            $this->workItems->add($workItem);
            $workItem->setProject($this);
        }

        return $this;
    }

    public function removeWorkItem(WorkItem $workItem): static
    {
        if ($this->workItems->removeElement($workItem)) {
            // set the owning side to null (unless already changed)
            if ($workItem->getProject() === $this) {
                $workItem->setProject(null);
            }
        }

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getLead(): ?User
    {
        return $this->lead;
    }

    public function setLead(?User $lead): static
    {
        $this->lead = $lead;

        return $this;
    }

    public function getDefaultAssignee(): ?User
    {
        return $this->defaultAssignee;
    }

    public function setDefaultAssignee(?User $defaultAssignee): static
    {
        $this->defaultAssignee = $defaultAssignee;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

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

    public function getSlug(): ?string
    {
        return $this->slug;
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

}
