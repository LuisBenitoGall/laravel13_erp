<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\UnitStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Unidades globales portadas de v1 (Viudavila) con IDs y status preservados.
 *
 * Fuente: `database/data/units/units.json`. Upsert por `id`. Status origen
 * 1/`true`/`active` → `active`; resto → `inactive`. Sin columna `translations`.
 * Si falta el JSON avisa y se omite. NO está en `DatabaseSeeder`; el Index no importa.
 *
 * Invocación: `php artisan db:seed --class=UnitsSeeder`
 */
class UnitsSeeder extends Seeder
{
    private const CHUNK = 100;

    private const COLUMNS = ['name', 'slug', 'symbol', 'status', 'deleted_at', 'created_at', 'updated_at'];

    public function run(): void
    {
        $path = database_path('data/units/units.json');

        if (! is_file($path)) {
            $this->command?->warn("UnitsSeeder: {$path} no existe; se omite.");

            return;
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            $this->command?->warn("UnitsSeeder: {$path} no contiene un array; se omite.");

            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];

        foreach ($decoded as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            $row = ['id' => (int) $item['id']];

            foreach (self::COLUMNS as $column) {
                $row[$column] = $item[$column] ?? null;
            }

            $row['status'] = $this->mapStatus($item['status'] ?? null);
            $row['created_at'] ??= $now;
            $row['updated_at'] ??= $now;

            if (is_string($row['created_at'])) {
                $row['created_at'] = $this->normalizeTimestamp($row['created_at']);
            }

            if (is_string($row['updated_at'])) {
                $row['updated_at'] = $this->normalizeTimestamp($row['updated_at']);
            }

            $rows[] = $row;
        }

        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            DB::table('units')->upsert($chunk, ['id'], self::COLUMNS);
        }

        $this->resetAutoIncrement();

        $this->command?->info('UnitsSeeder: units → '.count($rows).' filas.');
    }

    private function mapStatus(mixed $status): string
    {
        if ($status === 1 || $status === true || $status === '1' || $status === 'active') {
            return UnitStatus::Active->value;
        }

        return UnitStatus::Inactive->value;
    }

    private function normalizeTimestamp(string $value): string
    {
        $value = str_replace('T', ' ', rtrim($value, 'Z'));

        return preg_replace('/\.\d+$/', '', $value) ?? $value;
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table('units')->max('id')) + 1;
        DB::statement("ALTER TABLE `units` AUTO_INCREMENT = {$next}");
    }
}
