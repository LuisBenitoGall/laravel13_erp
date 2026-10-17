<?php

use App\Support\Strings\UniqueSlug;

it('devuelve el slug base si no colisiona', function () {
    $slug = UniqueSlug::from('Acme Corp', fn (string $s): bool => false);

    expect($slug)->toBe('acme-corp');
});

it('añade sufijo timestamp ante colisión', function () {
    $taken = ['acme-corp' => true];

    $slug = UniqueSlug::from('Acme Corp', function (string $s) use (&$taken): bool {
        return isset($taken[$s]);
    });

    expect($slug)->toStartWith('acme-corp-')
        ->and($slug)->not->toBe('acme-corp')
        ->and(strlen($slug))->toBeGreaterThan(strlen('acme-corp'));
});

it('reintenta si el slug con timestamp también colisiona', function () {
    $taken = [];

    $slug = UniqueSlug::from('Demo', function (string $s) use (&$taken): bool {
        if ($s === 'demo' || preg_match('/^demo-\d{14}$/', $s) === 1) {
            return true;
        }

        return isset($taken[$s]);
    });

    expect($slug)->toStartWith('demo-')
        ->and($slug)->toMatch('/^demo-\d{14}-[a-z0-9]+$/');
});
