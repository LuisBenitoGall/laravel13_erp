<?php

namespace App\Support\Strings;

use Illuminate\Support\Str;

/**
 * Genera slug único: base Str::slug; ante colisión añade sufijo timestamp.
 */
final class UniqueSlug
{
    /**
     * @param  callable(string): bool  $exists  true si el slug ya está en uso
     */
    public static function from(string $source, callable $exists): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            $base = 'item';
        }

        if (strlen($base) > 48) {
            $base = substr($base, 0, 48);
        }

        if (! $exists($base)) {
            return $base;
        }

        $candidate = $base.'-'.now()->format('YmdHis');

        if (! $exists($candidate)) {
            return $candidate;
        }

        do {
            $candidate = $base.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(4));
        } while ($exists($candidate));

        return $candidate;
    }
}
