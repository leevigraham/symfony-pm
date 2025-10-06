<?php

namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SecondsToHumanTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        if (!is_int($value)) {
            throw new TransformationFailedException('Expected integer.');
        }

        $hours = intdiv($value, 3600);
        $minutes = intdiv($value % 3600, 60);

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0 || $hours === 0) {
            $parts[] = $minutes . 'm';
        }

        return implode(' ', $parts);
    }

    public function reverseTransform($value): ?int
    {
        if (null === $value || trim($value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        preg_match_all('/(\d+)\s*(h|m)/i', $value, $matches, PREG_SET_ORDER);

        $seconds = 0;

        foreach ($matches as [, $number, $unit]) {
            if ('h' === strtolower($unit)) {
                $seconds += ((int)$number) * 3600;
            } elseif ('m' === strtolower($unit)) {
                $seconds += ((int)$number) * 60;
            }
        }

        return $seconds;
    }


}
