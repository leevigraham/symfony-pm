<?php

namespace App\Twig\Runtime;

use App\DataTransformer\SecondsToHumanTransformer;
use Twig\Extension\RuntimeExtensionInterface;

readonly class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private SecondsToHumanTransformer $secondsToHumanTransformer
    )
    {
        // Inject dependencies if needed
    }

    public function secondsToHuman($value): string
    {
        return $this->secondsToHumanTransformer->transform($value);
    }

    public function gravatarUrl(string $email, int $size = 80): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/$hash?s=$size&d=blank";
    }
}
