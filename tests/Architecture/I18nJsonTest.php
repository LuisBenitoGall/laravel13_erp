<?php

use Illuminate\Support\Facades\File;

it('tiene el mismo conjunto de claves en es, en y ca', function () {
    $locales = config('i18n.supported', ['es', 'en', 'ca']);
    $keySets = [];

    foreach ($locales as $locale) {
        $path = lang_path($locale.'.json');
        expect(File::exists($path))->toBeTrue("Falta lang/{$locale}.json");

        /** @var array<string, string> $dict */
        $dict = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $keys = array_keys($dict);
        sort($keys);
        $keySets[$locale] = $keys;
    }

    $baseline = $keySets[$locales[0]];
    foreach ($locales as $locale) {
        expect($keySets[$locale])->toBe($baseline, "Claves distintas en {$locale}");
    }
});

it('ordena las claves alfabéticamente en cada JSON de idioma', function () {
    foreach (config('i18n.supported', ['es', 'en', 'ca']) as $locale) {
        $path = lang_path($locale.'.json');
        /** @var array<string, string> $dict */
        $dict = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $keys = array_keys($dict);
        $sorted = $keys;
        sort($sorted, SORT_STRING);

        expect($keys)->toBe($sorted, "lang/{$locale}.json no está ordenado A–Z");
    }
});

it('tiene fichero lang/{locale}.json por cada locale soportado', function () {
    foreach (config('i18n.supported', ['es', 'en', 'ca']) as $locale) {
        expect(File::exists(lang_path($locale.'.json')))->toBeTrue();
    }
});

it('usa claves castellanas snake_case sin puntos (^[a-z0-9_]+$)', function () {
    $failures = [];

    foreach (config('i18n.supported', ['es', 'en', 'ca']) as $locale) {
        /** @var array<string, string> $dict */
        $dict = json_decode(File::get(lang_path($locale.'.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach (array_keys($dict) as $key) {
            if (! preg_match('/^[a-z0-9_]+$/', $key)) {
                $failures[] = "{$locale}: {$key}";
            }
        }
    }

    expect($failures)->toBeEmpty('Claves i18n inválidas: '.implode(', ', $failures));
});
