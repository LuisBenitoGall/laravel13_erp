<?php

namespace App\Domains\Admin\Support;

/** Normalización SWIFT/BIC al estilo v1 (pad a 11 con X). */
final class SwiftCode
{
    public static function normalize(?string $swift): ?string
    {
        if ($swift === null) {
            return null;
        }

        $trimmed = trim($swift);

        if ($trimmed === '') {
            return null;
        }

        $upper = strtoupper($trimmed);

        if (strlen($upper) < 11) {
            return str_pad($upper, 11, 'X');
        }

        return substr($upper, 0, 11);
    }
}
