<?php

namespace App\Entity;

use App\Repository\SequenceCounterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SequenceCounterRepository::class)]
class SequenceCounter
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $scope = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $lastNumber = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function getLastNumber(): ?int
    {
        return $this->lastNumber;
    }

    public function setLastNumber(int $lastNumber): static
    {
        $this->lastNumber = $lastNumber;

        return $this;
    }
}
