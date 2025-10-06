<?php

namespace App\Twig\Runtime;

use App\DataTransformer\SecondsToHumanTransformer;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SecondsToHumanTransformer $secondsToHumanTransformer
    )
    {
        // Inject dependencies if needed
    }

    public function secondsToHuman($value): string
    {
        return $this->secondsToHumanTransformer->transform($value);
    }
}
