<?php

namespace App\Support\Html;

/**
 * Allowlist HTML alineada a TipTap StarterKit (p, br, strong, em, lists, h2/h3, a).
 * Sin dependencia Composer nueva. Ningún atributo arbitrario sobrevive;
 * solo `<a href>` con http(s)/mailto. Tags peligrosos se eliminan con su contenido.
 */
final class AllowedHtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><em><ul><ol><li><h2><h3><a>';

    /** @var list<string> */
    private const DANGEROUS_TAGS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'noscript',
    ];

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

                return '<'.$tag.'>';
            },
            $clean,
        ) ?? $clean;

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
}
