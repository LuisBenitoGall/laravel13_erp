<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\GeographyStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Geografía global (Country → Province → Town) portada de v1 con IDs preservados.
 *
 * Fuente: `database/data/geography/{countries,provinces,towns}.json` (array de filas
 * con `id`). Idempotente: upsert por `id`. Status v1 1/0 → `active`/`inactive`.
 * Si un fichero no existe se omite con aviso. No re-sembrar si ya hay FKs consumidoras.
 *
 * Invocación: `php artisan db:seed --class=GeographySeeder`
 */
class GeographySeeder extends Seeder
{
    private const CHUNK = 500;

    public function run(): void
    {
        $this->load('countries', ['name', 'slug', 'code', 'alfa2', 'alfa3', 'flag', 'status', 'deleted_at', 'created_at', 'updated_at']);
        $this->load('provinces', ['name', 'slug', 'country_id', 'status', 'deleted_at', 'created_at', 'updated_at']);
        $this->load('towns', ['name', 'slug', 'province_id', 'status', 'deleted_at', 'created_at', 'updated_at']);
    }

    /** @param  list<string>  $columns */
    private function load(string $table, array $columns): void
    {
        $path = database_path("data/geography/{$table}.json");

        if (! is_file($path)) {
            $this->command?->warn("GeographySeeder: {$path} no existe; se omite {$table}.");

            return;
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            $this->command?->warn("GeographySeeder: {$path} no contiene un array; se omite {$table}.");

            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];

        foreach ($decoded as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            $row = ['id' => (int) $item['id']];

            foreach ($columns as $column) {
                $row[$column] = $item[$column] ?? null;
            }

            $row['status'] = $this->normalizeStatus($row['status']);
            $row['created_at'] ??= $now;
            $row['updated_at'] ??= $now;

            $rows[] = $row;
        }

        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            DB::table($table)->upsert($chunk, ['id'], $columns);
        }

        $this->resetAutoIncrement($table);

        $this->command?->info("GeographySeeder: {$table} → ".count($rows).' filas.');
    }

    private function normalizeStatus(mixed $status): string
    {
        if ($status instanceof GeographyStatus) {
            return $status->value;
        }

        return match (true) {
            $status === 0, $status === '0', $status === false, $status === GeographyStatus::Inactive->value => GeographyStatus::Inactive->value,
            default => GeographyStatus::Active->value,
        };
    }

    private function resetAutoIncrement(string $table): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table($table)->max('id')) + 1;
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
    }
}
