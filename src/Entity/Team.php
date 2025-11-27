<?php

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Entity\Trait\TimestampableEntity;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    /**
     * @var Collection<int, TeamMember>
     */
    #[ORM\OneToMany(
        targetEntity: TeamMember::class,
        mappedBy: 'team',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    public Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function addMember(TeamMember $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->team = $this;
        }

        return $this;
    }

    public function removeMember(TeamMember $member): static
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->team === $this) {
                $member->team =null;
            }
        }

        return $this;
    }
}
