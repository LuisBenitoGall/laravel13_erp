<?php

namespace App\Console\Commands;

use App\Domains\Admin\Models\MediaAsset;
use App\Models\UserImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('erp:migrate-public-media-to-local {--dry-run : Solo informar sin copiar ni borrar} {--delete-public : Borrar del disco public tras copiar con éxito}')]
#[Description('Copia paths de user_images y media_assets del disco public al local (idempotente)')]
class MigratePublicMediaToLocalCommand extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deletePublic = (bool) $this->option('delete-public');

        $copied = 0;
        $skipped = 0;
        $missing = 0;

        UserImage::query()
            ->withTrashed()
            ->orderBy('id')
            ->each(function (UserImage $image) use ($dryRun, $deletePublic, &$copied, &$skipped, &$missing): void {
                $this->migratePath(
                    (string) $image->image,
                    $dryRun,
                    $deletePublic,
                    $copied,
                    $skipped,
                    $missing,
                );
            });

        MediaAsset::query()
            ->withTrashed()
            ->orderBy('id')
            ->each(function (MediaAsset $asset) use ($dryRun, $deletePublic, &$copied, &$skipped, &$missing): void {
                $this->migratePath(
                    (string) $asset->path,
                    $dryRun,
                    $deletePublic,
                    $copied,
                    $skipped,
                    $missing,
                );
            });

        $this->info("migrate-public-media-to-local — copied: {$copied}, skipped: {$skipped}, missing: {$missing}".($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }

    private function migratePath(
        string $path,
        bool $dryRun,
        bool $deletePublic,
        int &$copied,
        int &$skipped,
        int &$missing,
    ): void {
        if ($path === '') {
            $skipped++;

            return;
        }

        $local = Storage::disk('local');
        $public = Storage::disk('public');

        if ($local->exists($path)) {
            $skipped++;

            return;
        }

        if (! $public->exists($path)) {
            $missing++;

            return;
        }

        if ($dryRun) {
            $copied++;

            return;
        }

        $local->put($path, $public->get($path));
        $copied++;

        if ($deletePublic) {
            $public->delete($path);
        }
    }
}
