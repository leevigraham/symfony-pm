<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Trait\TimestampableEntity;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email]
    #[Assert\NotBlank]
    public ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    public array $roles = [] {
        get {
            $roles = $this->roles;
            // guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';
            return array_unique($roles);
        }
    }

    #[ORM\Column]
    public ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public ?string $displayName = null;

    #[ORM\Column]
    public ?bool $emailVerified = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $googleAccountId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Timezone]
    public ?string $timezone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Locale]
    public ?string $locale = null;

    /**
     * @var Collection<int, TeamMember>
     */
    #[ORM\OneToMany(targetEntity: TeamMember::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $memberships;

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->displayName;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function addTeamMember(TeamMember $teamMember): static
    {
        if (!$this->memberships->contains($teamMember)) {
            $this->memberships->add($teamMember);
            $teamMember->user = $this;
        }

        return $this;
    }

    public function removeTeamMember(TeamMember $teamMember): static
    {
        if ($this->memberships->removeElement($teamMember)) {
            // set the owning side to null (unless already changed)
            if ($teamMember->user === $this) {
                $teamMember->user = null;
            }
        }

        return $this;
    }

}
