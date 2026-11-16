<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use App\Support\I18n\LocaleResolver;
use Illuminate\Support\Facades\Cache;

/**
 * Mapa code → tip Help por locale (cache; invalidar en mutate).
 *
 * @phpstan-type HelpTip array{title: string, excerpt: string, content: string, referrer: string|null, video: string|null}
 */
class HelpContentMap
{
    public function __construct(
        private readonly LocaleResolver $locales,
    ) {}

    /** @return array<string, HelpTip> */
    public function forLocale(string $locale): array
    {
        if (! $this->locales->isSupported($locale)) {
            $locale = $this->locales->default();
        }

        /** @var array<string, HelpTip> */
        return Cache::remember(
            $this->cacheKey($locale),
            now()->addDay(),
            fn (): array => $this->build($locale),
        );
    }

    public function forget(): void
    {
        foreach ($this->locales->supported() as $locale) {
            Cache::forget($this->cacheKey($locale));
        }
    }

    public function cacheKey(string $locale): string
    {
        return 'contents.help.map.'.$locale;
    }

    /** @return array<string, HelpTip> */
    private function build(string $locale): array
    {
        $default = $this->locales->default();
        $map = [];

        $contents = Content::query()
            ->where('kind', ContentKind::Help)
            ->where('status', ContentStatus::Active)
            ->whereNull('company_id')
            ->get(['code', 'title', 'excerpt', 'content', 'referrer', 'video']);

        foreach ($contents as $content) {
            $map[$content->code] = [
                'title' => $this->translated($content, 'title', $locale, $default),
                'excerpt' => $this->translated($content, 'excerpt', $locale, $default),
                'content' => $this->translated($content, 'content', $locale, $default),
                'referrer' => $content->referrer,
                'video' => $content->video,
            ];
        }

        return $map;
    }

    private function translated(Content $content, string $field, string $locale, string $default): string
    {
        $value = (string) $content->getTranslation($field, $locale, false);
        if ($value !== '') {
            return $value;
        }

        return (string) $content->getTranslation($field, $default, false);
    }
}
