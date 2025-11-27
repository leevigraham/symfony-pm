<?php

namespace App\Entity\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait BlameableEntity
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Gedmo\Blameable(on: 'create')]
    public ?User $createdBy;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Gedmo\Blameable(on: 'update')]
    public ?User $updatedBy;
}

