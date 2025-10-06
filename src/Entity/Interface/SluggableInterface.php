<?php

namespace App\Entity\Interface;

interface SluggableInterface
{
    public function getSlug(): ?string;
}
