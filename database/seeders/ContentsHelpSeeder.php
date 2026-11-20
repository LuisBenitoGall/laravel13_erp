<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Services\HelpContentMap;
use Illuminate\Database\Seeder;

/**
 * Contents Help fijos (anclas) desde `database/data/contents_help.json`.
 * Upsert por `code`; no borra Help ajenos al fichero.
 * NO está en DatabaseSeeder.
 *
 * Invocación: `php artisan db:seed --class=ContentsHelpSeeder`
 */
class ContentsHelpSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/contents_help.json');

        if (! is_file($path)) {
            $this->command?->warn("ContentsHelpSeeder: {$path} no existe; se omite.");

            return;
        }

        /** @var mixed $decoded */
        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            $this->command?->warn("ContentsHelpSeeder: {$path} no contiene un array; se omite.");

            return;
        }

        $this->renameLegacyCompanyNif();

        $upserted = 0;

        foreach ($decoded as $item) {
            if (! is_array($item) || ! is_string($item['code'] ?? null) || $item['code'] === '') {
                continue;
            }

            $code = (string) $item['code'];
            $kind = ContentKind::tryFrom((string) ($item['kind'] ?? ContentKind::Help->value))
                ?? ContentKind::Help;

            if ($kind !== ContentKind::Help) {
                continue;
            }

            $status = ContentStatus::tryFrom((string) ($item['status'] ?? ContentStatus::Active->value))
                ?? ContentStatus::Active;

            $content = Content::withTrashed()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => is_string($item['name'] ?? null) ? $item['name'] : $code,
                    'kind' => ContentKind::Help,
                    'company_id' => null,
                    'title' => $this->localeMap($item['title'] ?? null),
                    'excerpt' => $this->localeMap($item['excerpt'] ?? null),
                    'content' => $this->localeMap($item['content'] ?? null),
                    'referrer' => $this->stringList($item['referrer'] ?? null),
                    'video' => $this->stringList($item['video'] ?? null),
                    'status' => $status,
                ],
            );

            if ($content->trashed()) {
                $content->restore();
            }

            $upserted++;
        }

        app(HelpContentMap::class)->forget();

        $this->command?->info("ContentsHelpSeeder: {$upserted} Help upserted por code.");
    }

    /**
     * One-shot: `company-nif` → `companies.nif` si el destino libre.
     * Idempotente; no toca filas ajenas.
     */
    private function renameLegacyCompanyNif(): void
    {
        $legacy = Content::withTrashed()->where('code', 'company-nif')->first();
        if ($legacy === null) {
            return;
        }

        $targetExists = Content::withTrashed()->where('code', 'companies.nif')->exists();
        if ($targetExists) {
            if (! $legacy->trashed()) {
                $legacy->delete();
            }

            return;
        }

        $legacy->forceFill(['code' => 'companies.nif'])->save();
        if ($legacy->trashed()) {
            $legacy->restore();
        }
    }

    /** @return array<string, string> */
    private function localeMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $locale => $text) {
            if (! is_string($locale)) {
                continue;
            }
            $out[$locale] = is_string($text) ? $text : '';
        }

        return $out;
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                $out[] = trim($item);
            }
        }

        return $out;
    }
}
