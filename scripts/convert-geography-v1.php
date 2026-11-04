<?php

declare(strict_types=1);

/**
 * One-shot: Viudavila seeders → database/data/geography/*.json (status string).
 *
 * Usage: php scripts/convert-geography-v1.php
 */
$v1Base = 'C:/laragon/www/laravel8_erp_viudavila/database/seeders/Viudavila';
$outDir = dirname(__DIR__).'/database/data/geography';

if (! is_dir($outDir) && ! mkdir($outDir, 0777, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$files = [
    'countries' => $v1Base.'/CountriesTableSeeder.php',
    'provinces' => $v1Base.'/ProvincesTableSeeder.php',
    'towns' => $v1Base.'/TownsTableSeeder.php',
];

foreach ($files as $name => $path) {
    if (! is_file($path)) {
        fwrite(STDERR, "Missing {$path}\n");
        exit(1);
    }

    $src = file_get_contents($path);
    if ($src === false) {
        fwrite(STDERR, "Cannot read {$path}\n");
        exit(1);
    }

    $rows = [];
    $offset = 0;
    $needle = '->insert(';

    while (($start = strpos($src, $needle, $offset)) !== false) {
        $i = $start + strlen($needle);
        $depth = 0;
        $len = strlen($src);
        $end = null;

        for (; $i < $len; $i++) {
            $ch = $src[$i];
            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
                $depth--;
            }
        }

        if ($end === null) {
            fwrite(STDERR, "Unbalanced insert() in {$path}\n");
            exit(1);
        }

        $payload = substr($src, $start + strlen($needle), $end - ($start + strlen($needle)));
        $payload = preg_replace("/'status'\\s*=>\\s*1\\b/", "'status' => 'active'", $payload) ?? $payload;
        $payload = preg_replace("/'status'\\s*=>\\s*0\\b/", "'status' => 'inactive'", $payload) ?? $payload;

        /** @var list<array<string, mixed>> $chunk */
        $chunk = eval('return '.$payload.';');
        foreach ($chunk as $row) {
            $rows[] = $row;
        }

        $offset = $end + 1;
    }

    if ($rows === []) {
        fwrite(STDERR, "No insert() in {$path}\n");
        exit(1);
    }

    $dest = $outDir.'/'.$name.'.json';
    file_put_contents($dest, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    echo $name.': '.count($rows).' → '.$dest.PHP_EOL;
}
