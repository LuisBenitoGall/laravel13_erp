<?php

namespace Database\Seeders;

use App\Domains\Banks\Enums\BankStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Bancos globales portados de v1 (Viudavila) con IDs y status preservados.
 *
 * Fuente: `database/data/banks/banks.enriched.json` (array de filas con `id`).
 * Idempotente: upsert por `id`. Status origen 1/`true`/`active` → `active`; resto →
 * `inactive`. Conserva swift / swift_source / swift_updated_at si vienen en el JSON.
 * Si el fichero no existe se omite con aviso. Resetea AUTO_INCREMENT (MySQL).
 * NO está en `DatabaseSeeder` y el Index nunca importa.
 *
 * Invocación: `php artisan db:seed --class=BanksSeeder`
 */
class BanksSeeder extends Seeder
{
    private const CHUNK = 200;

    private const COLUMNS = [
        'name',
        'slug',
        'tradename',
        'lei',
        'eu_code',
        'supervisor_code',
        'swift',
        'swift_source',
        'swift_updated_at',
        'status',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function run(): void
    {
        $path = database_path('data/banks/banks.enriched.json');

        if (! is_file($path)) {
            $this->command?->warn("BanksSeeder: {$path} no existe; se omite.");

            return;
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            $this->command?->warn("BanksSeeder: {$path} no contiene un array; se omite.");

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
                $row['created_at'] = str_replace('T', ' ', rtrim($row['created_at'], 'Z'));
                $row['created_at'] = preg_replace('/\.\d+$/', '', $row['created_at']) ?? $row['created_at'];
            }

            if (is_string($row['updated_at'])) {
                $row['updated_at'] = str_replace('T', ' ', rtrim($row['updated_at'], 'Z'));
                $row['updated_at'] = preg_replace('/\.\d+$/', '', $row['updated_at']) ?? $row['updated_at'];
            }

            if (is_string($row['swift_updated_at'])) {
                $row['swift_updated_at'] = str_replace('T', ' ', rtrim($row['swift_updated_at'], 'Z'));
                $row['swift_updated_at'] = preg_replace('/\.\d+$/', '', $row['swift_updated_at']) ?? $row['swift_updated_at'];
            }

            $rows[] = $row;
        }

        $columns = self::COLUMNS;

        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            DB::table('banks')->upsert($chunk, ['id'], $columns);
        }

        $this->resetAutoIncrement();

        $this->command?->info('BanksSeeder: banks → '.count($rows).' filas.');
    }

    private function mapStatus(mixed $status): string
    {
        if ($status === 1 || $status === true || $status === '1' || $status === 'active') {
            return BankStatus::Active->value;
        }

        return BankStatus::Inactive->value;
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table('banks')->max('id')) + 1;
        DB::statement("ALTER TABLE `banks` AUTO_INCREMENT = {$next}");
    }
}
