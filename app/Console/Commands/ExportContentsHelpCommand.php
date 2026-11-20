<?php

namespace App\Console\Commands;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use Illuminate\Console\Command;

/**
 * Dump Help Active globales (`company_id` null) → `database/data/contents_help.json`.
 *
 * Uso: editar tips en un entorno → `php artisan contents:export-help` →
 * commit del JSON → `db:seed --class=ContentsHelpSeeder` en otros entornos.
 */
class ExportContentsHelpCommand extends Command
{
    protected $signature = 'contents:export-help
                            {--path= : Ruta de salida (default database/data/contents_help.json)}';

    protected $description = 'Exporta Contents Help Active globales al fixture JSON versionado';

    public function handle(): int
    {
        $path = $this->option('path') ?: database_path('data/contents_help.json');

        $rows = Content::query()
            ->where('kind', ContentKind::Help)
            ->where('status', ContentStatus::Active)
            ->whereNull('company_id')
            ->orderBy('code')
            ->get();

        $payload = $rows->map(function (Content $content): array {
            return [
                'code' => $content->code,
                'name' => $content->name,
                'kind' => ContentKind::Help->value,
                'status' => ContentStatus::Active->value,
                'title' => $content->getTranslations('title'),
                'excerpt' => $content->getTranslations('excerpt'),
                'content' => $content->getTranslations('content'),
                'referrer' => is_array($content->referrer) ? $content->referrer : [],
                'video' => is_array($content->video) ? $content->video : [],
            ];
        })->values()->all();

        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error("No se pudo crear el directorio {$dir}");

            return self::FAILURE;
        }

        file_put_contents(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
        );

        $this->info(sprintf('Exportados %d Help Active → %s', count($payload), $path));

        return self::SUCCESS;
    }
}
