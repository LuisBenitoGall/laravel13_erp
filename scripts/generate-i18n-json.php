<?php

declare(strict_types=1);

/**
 * Re-sorts lang/{es,en,ca}.json A–Z and verifies parity + key shape (^[a-z0-9_]+$).
 * Source of truth: the JSON files themselves (castellano snake_case keys).
 *
 * Usage: php scripts/generate-i18n-json.php
 */
$root = dirname(__DIR__);
$locales = ['es', 'en', 'ca'];
/** @var array<string, list<string>> $keySets */
$keySets = [];

foreach ($locales as $locale) {
    $path = $root.'/lang/'.$locale.'.json';
    if (! is_file($path)) {
        fwrite(STDERR, "Missing {$path}\n");
        exit(1);
    }

    /** @var array<string, string> $dict */
    $dict = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

    foreach (array_keys($dict) as $key) {
        if (! preg_match('/^[a-z0-9_]+$/', $key)) {
            fwrite(STDERR, "Invalid key in {$locale}: {$key}\n");
            exit(1);
        }
    }

    ksort($dict, SORT_STRING);
    file_put_contents(
        $path,
        json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
    );

    $keys = array_keys($dict);
    $keySets[$locale] = $keys;
    echo "{$locale}: ".count($dict)." keys\n";
}

$baseline = $keySets[$locales[0]];
$ok = true;
foreach ($locales as $locale) {
    if ($keySets[$locale] !== $baseline) {
        fwrite(STDERR, "parity FAIL: {$locale} differs from {$locales[0]}\n");
        $ok = false;
    }
}

echo $ok ? "parity: ok\n" : "parity: FAIL\n";
exit($ok ? 0 : 1);
