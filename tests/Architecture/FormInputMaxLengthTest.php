<?php

use Illuminate\Support\Facades\File;

it('el Input del kit declara DEFAULT_INPUT_MAX_LENGTH 255 y lo usa como default', function () {
    $path = resource_path('js/Components/ui/input.tsx');
    expect(File::exists($path))->toBeTrue('Falta resources/js/Components/ui/input.tsx');

    $source = File::get($path);

    expect($source)
        ->toContain('DEFAULT_INPUT_MAX_LENGTH = 255')
        ->toContain('maxLength = DEFAULT_INPUT_MAX_LENGTH');
});

it('páginas Auth no usan <input> de texto crudo', function () {
    $dir = resource_path('js/Pages/Auth');
    expect(File::isDirectory($dir))->toBeTrue();

    $textTypes = ['text', 'email', 'password', 'search', 'tel', 'url'];
    $excludedTypes = ['checkbox', 'radio', 'hidden', 'file', 'submit', 'button'];
    $failures = [];

    foreach (File::files($dir) as $file) {
        if ($file->getExtension() !== 'tsx') {
            continue;
        }

        $source = File::get($file->getPathname());
        $relative = 'resources/js/Pages/Auth/'.$file->getFilename();

        // Solo el tag HTML `<input` (minúsculas); no el componente React `<Input`.
        if (! preg_match_all('/<input\b([^>]*)>/', $source, $matches, PREG_SET_ORDER)) {
            continue;
        }

        foreach ($matches as $match) {
            $attrs = $match[1];

            if (preg_match('/\btype\s*=\s*["\']([^"\']+)["\']/', $attrs, $typeMatch)) {
                $type = strtolower($typeMatch[1]);
            } elseif (preg_match('/\btype\s*=\s*\{([^}]+)\}/', $attrs, $exprMatch)) {
                $expr = $exprMatch[1];
                // Expresiones dinámicas (p. ej. showPassword ? 'text' : 'password') = texto.
                if (preg_match("/['\"](checkbox|radio|hidden|file|submit|button)['\"]/", $expr)) {
                    continue;
                }
                $type = 'text';
            } else {
                $type = 'text';
            }

            if (in_array($type, $excludedTypes, true)) {
                continue;
            }

            if (in_array($type, $textTypes, true) || $type === 'text') {
                $failures[] = "{$relative}: <input type=\"{$type}\"> — usar Components/ui/input";
            }
        }
    }

    expect($failures)->toBeEmpty(implode("\n", $failures));
});
