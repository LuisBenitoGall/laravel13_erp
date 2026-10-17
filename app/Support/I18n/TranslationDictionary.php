<?php

namespace App\Support\I18n;

use Illuminate\Support\Facades\File;

final class TranslationDictionary
{
    /** @return array<string, string> */
    public function forLocale(string $locale): array
    {
        $path = lang_path($locale.'.json');

        if (! File::exists($path)) {
            return [];
        }

        /** @var array<string, string>|null $decoded */
        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
