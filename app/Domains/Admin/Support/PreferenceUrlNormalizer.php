<?php

namespace App\Domains\Admin\Support;

use Illuminate\Validation\ValidationException;

final class PreferenceUrlNormalizer
{
    /**
     * Normaliza a path (+ query) sin scheme/host/hash.
     * Protocol-relative (`//…`) se deja tal cual para que falle isAllowedAppPath.
     */
    public static function normalize(string $url): string
    {
        $url = trim($url);
        $url = explode('#', $url, 2)[0];

        if ($url === '') {
            return '/';
        }

        if (str_starts_with($url, '//')) {
            return $url;
        }

        if (str_contains($url, '://')) {
            $parts = parse_url($url);
            if (! is_array($parts)) {
                return '/';
            }

            $path = $parts['path'] ?? '/';
            if ($path === '') {
                $path = '/';
            }

            $query = isset($parts['query']) && $parts['query'] !== ''
                ? '?'.$parts['query']
                : '';

            return $path.$query;
        }

        if (! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }

        return $url;
    }

    public static function isAllowedAppPath(string $normalized): bool
    {
        if ($normalized === '' || str_contains($normalized, '://')) {
            return false;
        }

        if (str_starts_with($normalized, '//')) {
            return false;
        }

        $pathOnly = explode('?', $normalized, 2)[0];

        if ($pathOnly === '' || ! str_starts_with($pathOnly, '/') || str_starts_with($pathOnly, '//')) {
            return false;
        }

        $allowedRoots = [
            'dashboard',
            'account',
            'users',
            'companies',
            'settings',
            'products',
            'rrhh',
            'deliveries',
        ];

        foreach ($allowedRoots as $root) {
            if ($pathOnly === '/'.$root || str_starts_with($pathOnly, '/'.$root.'/')) {
                return true;
            }
        }

        return false;
    }

    /** @throws ValidationException */
    public static function forStorage(string $url): string
    {
        $normalized = self::normalize($url);

        if (! self::isAllowedAppPath($normalized)) {
            throw ValidationException::withMessages([
                'url' => __('favorito_url_invalida'),
            ]);
        }

        return $normalized;
    }
}
