<?php

namespace App\Support;

final class CsName
{
    public static function key(?string $name): string
    {
        $normalized = mb_strtolower(trim((string) $name));

        return match ($normalized) {
            'bela' => 'bella',
            default => $normalized,
        };
    }
}
