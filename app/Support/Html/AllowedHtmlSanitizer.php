<?php

namespace App\Support\Html;

/**
 * Allowlist HTML alineada a TipTap StarterKit + Image (p, br, strong, em, lists,
 * h2/h3, a, img). Sin dependencia Composer nueva. Ningún atributo arbitrario
 * sobrevive; solo `<a href>` seguro y `<img src alt>` seguros.
 */
final class AllowedHtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><em><ul><ol><li><h2><h3><a><img>';

    /** @var list<string> */
    private const DANGEROUS_TAGS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'noscript',
    ];

    /**
     * @param  array<string, mixed>  $map
     * @return array<string, string|null>
     */
    public function sanitizeLocaleMap(array $map, bool $plain = false): array
    {
        $out = [];
        foreach ($map as $locale => $value) {
            $key = (string) $locale;
            $string = is_string($value) ? $value : null;
            if ($plain) {
                $out[$key] = $string !== null ? trim(strip_tags($string)) : null;
                if ($out[$key] === '') {
                    $out[$key] = null;
                }
            } else {
                $out[$key] = $this->sanitize($string);
            }
        }

        return $out;
    }

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $trimmed = trim($html);
        if ($trimmed === '' || $trimmed === '<p></p>' || $trimmed === '<p><br></p>') {
            return null;
        }

        $clean = $this->stripDangerousElements($trimmed);
        $clean = strip_tags($clean, self::ALLOWED_TAGS);
        $clean = preg_replace_callback(
            '/<([a-z0-9]+)(\s[^>]*?)?\/?>/i',
            function (array $matches): string {
                $tag = strtolower($matches[1]);
                $attrs = $matches[2] ?? '';

                if ($tag === 'a') {
                    return $this->safeAnchorOpening($attrs);
                }

                if ($tag === 'img') {
                    return $this->safeImageTag($attrs);
                }

                return '<'.$tag.'>';
            },
            $clean,
        ) ?? $clean;

        $clean = preg_replace('/<\/img\s*>/i', '', $clean) ?? $clean;
        $clean = trim($clean);

        if ($clean === '' || $clean === '<p></p>' || $clean === '<p><br></p>') {
            return null;
        }

        return $clean;
    }

    private function stripDangerousElements(string $html): string
    {
        foreach (self::DANGEROUS_TAGS as $tag) {
            $html = preg_replace(
                '/<'.$tag.'\b[^>]*>.*?<\/'.$tag.'\s*>/is',
                '',
                $html,
            ) ?? $html;

            $html = preg_replace(
                '/<'.$tag.'\b[^>]*\/?>/i',
                '',
                $html,
            ) ?? $html;
        }

        return $html;
    }

    private function safeAnchorOpening(string $attrs): string
    {
        if (! preg_match('/href\s*=\s*(["\'])(.*?)\1/i', $attrs, $hrefMatch)) {
            return '<a>';
        }

        $href = trim(html_entity_decode($hrefMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (! preg_match('/^(https?:\/\/|mailto:)/i', $href)) {
            return '<a>';
        }

        $safe = htmlspecialchars($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return '<a href="'.$safe.'">';
    }

    private function safeImageTag(string $attrs): string
    {
        if (! preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $attrs, $srcMatch)) {
            return '';
        }

        $src = trim(html_entity_decode($srcMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (! $this->isSafeImageSrc($src)) {
            return '';
        }

        $alt = '';
        if (preg_match('/alt\s*=\s*(["\'])(.*?)\1/i', $attrs, $altMatch)) {
            $alt = html_entity_decode($altMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $safeSrc = htmlspecialchars($src, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeAlt = htmlspecialchars($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return '<img src="'.$safeSrc.'" alt="'.$safeAlt.'">';
    }

    private function isSafeImageSrc(string $src): bool
    {
        if ($src === '') {
            return false;
        }

        if (preg_match('/^(javascript|data|vbscript|file):/i', $src) === 1) {
            return false;
        }

        if (preg_match('/^https?:\/\//i', $src) === 1) {
            return true;
        }

        return str_starts_with($src, '/') && ! str_starts_with($src, '//');
    }
}
