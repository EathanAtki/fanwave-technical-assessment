<?php

declare(strict_types=1);

namespace App\DTO\Concerns;

trait NormalizesDtoValues
{
    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function toNullableNumber(mixed $value): int|float|null
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
