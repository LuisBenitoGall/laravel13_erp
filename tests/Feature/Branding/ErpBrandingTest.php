<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

it('publica wordmark y símbolo Coopmercio', function () {
    expect(File::exists(public_path('images/branding/coopmercio-horizontal-color.svg')))->toBeTrue()
        ->and(File::exists(public_path('images/branding/coopmercio-symbol-color.svg')))->toBeTrue()
        ->and(File::exists(public_path('images/erp-logo.svg')))->toBeFalse()
        ->and(config('branding.logo_url'))->toBe('/images/branding/coopmercio-horizontal-color.svg')
        ->and(config('branding.mark_url'))->toBe('/images/branding/coopmercio-symbol-color.svg');
});

it('sirve favicon, apple-touch y manifest Coopmercio', function () {
    foreach ([
        'favicon.svg',
        'favicon.ico',
        'favicon-16.png',
        'favicon-32.png',
        'favicon-48.png',
        'favicon-192.png',
        'favicon-512.png',
        'apple-touch-icon.png',
        'site.webmanifest',
    ] as $file) {
        expect(File::exists(public_path($file)))->toBeTrue("Falta public/{$file}");
    }

    $manifest = json_decode((string) File::get(public_path('site.webmanifest')), true, 512, JSON_THROW_ON_ERROR);

    expect($manifest['name'])->toBe('Coopmercio')
        ->and($manifest['short_name'])->toBe('Coopmercio')
        ->and($manifest['theme_color'])->toBe('#10A37F')
        ->and($manifest['icons'][0]['src'])->toBe('/favicon-192.png')
        ->and($manifest['icons'][1]['src'])->toBe('/favicon-512.png');

});

it('el shell Blade declara links de favicon y manifest', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    expect($html)->toContain('rel="icon" href="/favicon.svg"')
        ->and($html)->toContain('rel="icon" href="/favicon.ico"')
        ->and($html)->toContain('rel="apple-touch-icon" href="/apple-touch-icon.png"')
        ->and($html)->toContain('rel="manifest" href="/site.webmanifest"');
});

it('comparte branding Coopmercio en Inertia', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('branding.name', config('branding.name'))
            ->where('branding.logo_url', config('branding.logo_url'))
            ->where('branding.mark_url', config('branding.mark_url'))
            ->where('branding.colors.primary', '#10A37F')
            ->where('branding.colors.secondary', '#633D56'));
});

it('declara tokens de marca en config y tema CSS', function () {
    expect(config('branding.colors.primary'))->toBe('#10A37F')
        ->and(config('branding.colors.secondary'))->toBe('#633D56')
        ->and(config('branding.logo_url'))->toBe('/images/branding/coopmercio-horizontal-color.svg')
        ->and(config('branding.mark_url'))->toBe('/images/branding/coopmercio-symbol-color.svg');

    $css = (string) File::get(resource_path('css/app.css'));

    expect($css)->toContain('--color-brand: #10a37f')
        ->and($css)->toContain('--color-brand-secondary: #633d56')
        ->and($css)->toContain('--color-brand-hover:')
        ->and($css)->toContain('--color-brand-muted:')
        ->and($css)->toContain('--color-brand-ink:');
});

it('AppLayout usa mark_url en rail y logo_url expandido', function () {
    $source = (string) File::get(resource_path('js/Layouts/AppLayout.tsx'));

    expect($source)->toContain('railMode ? branding.mark_url : branding.logo_url')
        ->and($source)->toContain('bg-brand')
        ->and($source)->not->toContain('bg-teal-700');
});

it('.env.example y default de app.name usan Coopmercio', function () {
    $example = (string) File::get(base_path('.env.example'));
    $appConfig = (string) File::get(config_path('app.php'));

    expect($example)->toContain('APP_NAME=Coopmercio')
        ->and($example)->not->toContain('APP_NAME=Laravel')
        ->and($appConfig)->toContain("env('APP_NAME', 'Coopmercio')");
});

it('no quedan acentos teal de marca en UI producto', function () {
    $hits = [];
    $roots = [resource_path('js'), resource_path('css')];

    foreach ($roots as $root) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            if (! in_array(strtolower($file->getExtension()), ['tsx', 'ts', 'css'], true)) {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            if (preg_match('/teal-(50|700|800)|#0f766e/', $contents) === 1) {
                $hits[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
    }

    expect($hits)->toBe([]);
});
