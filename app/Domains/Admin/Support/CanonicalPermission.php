<?php

namespace App\Domains\Admin\Support;

use Illuminate\Support\Str;

/**
 * Clasifica nombres Spatie Permission: canónicos (5 acciones + *.access) vs custom.
 */
final class CanonicalPermission
{
    /** @var list<string> */
    public const ACTION_SUFFIXES = ['viewAny', 'view', 'create', 'update', 'delete'];

    /** Sufijos prohibidos en Create (acciones + access). */
    /** @var list<string> */
    public const RESERVED_SUFFIXES = ['viewAny', 'view', 'create', 'update', 'delete', 'access'];

    public static function isCanonical(string $name): bool
    {
        if (! str_contains($name, '.')) {
            return false;
        }

        if (str_ends_with($name, '.access')) {
            return true;
        }

        $suffix = Str::afterLast($name, '.');

        return in_array($suffix, self::ACTION_SUFFIXES, true);
    }

    public static function isReservedSuffix(string $suffix): bool
    {
        $normalized = Str::slug($suffix);

        foreach (self::RESERVED_SUFFIXES as $reserved) {
            if ($normalized === Str::slug($reserved)) {
                return true;
            }
        }

        return false;
    }

    public static function functionalitySlug(string $name): ?string
    {
        if (! str_contains($name, '.')) {
            return null;
        }

        return Str::before($name, '.');
    }

    public static function composeName(string $functionalitySlug, string $suffix): string
    {
        return $functionalitySlug.'.'.$suffix;
    }
}
