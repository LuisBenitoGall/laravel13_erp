<?php

declare(strict_types=1);

/**
 * One-shot: Viudavila CurrenciesTableSeeder → database/data/currencies/currencies.json
 *
 * Usage: php scripts/convert-currencies-v1.php
 */
$v1Path = 'C:/laragon/www/laravel8_erp_viudavila/database/seeders/Viudavila/CurrenciesTableSeeder.php';
$outDir = dirname(__DIR__).'/database/data/currencies';

if (! is_dir($outDir) && ! mkdir($outDir, 0777, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

if (! is_file($v1Path)) {
    fwrite(STDERR, "Missing {$v1Path}\n");
    exit(1);
}

$src = file_get_contents($v1Path);
if ($src === false) {
    fwrite(STDERR, "Cannot read {$v1Path}\n");
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
        fwrite(STDERR, "Unbalanced insert()\n");
        exit(1);
    }

    $payload = substr($src, $start + strlen($needle), $end - ($start + strlen($needle)));
    /** @var list<array<string, mixed>> $chunk */
    $chunk = eval('return '.$payload.';');
    foreach ($chunk as $row) {
        $rows[] = $row;
    }

    $offset = $end + 1;
}

if ($rows === []) {
    fwrite(STDERR, "No rows\n");
    exit(1);
}

$dest = $outDir.'/currencies.json';
file_put_contents($dest, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
echo 'currencies: '.count($rows).' → '.$dest.PHP_EOL;
