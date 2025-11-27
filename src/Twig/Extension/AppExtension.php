<?php

namespace App\Twig\Extension;

use App\DataTransformer\SecondsToHumanTransformer;
use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('seconds_to_human', [AppExtensionRuntime::class, 'secondsToHuman'], ['is_safe' => ['html']]),
            new TwigFilter('gravatar_url', [AppExtensionRuntime::class, 'gravatarUrl'], ['is_safe' => ['html']]),
        ];
    }
}
