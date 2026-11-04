<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Tipos de IVA v1 asignados a España (countries.id = 66).
 *
 * Fuente: `database/data/vat-types/iva-types.json`. Upsert por `id`. Status
 * 1→active / 0→inactive. Campo JSON `iva` → columna `rate`. Requiere
 * GeographySeeder (España id=66) previo. NO está en DatabaseSeeder; Index no importa.
 *
 * Invocación: `php artisan db:seed --class=VatTypesSeeder`
 */
class VatTypesSeeder extends Seeder
{
    public const SPAIN_COUNTRY_ID = 66;

    private const COLUMNS = [
        'country_id',
        'name',
        'rate',
        'equivalence_surcharge',
        'status',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function run(): void
    {
        if (! Country::query()->whereKey(self::SPAIN_COUNTRY_ID)->exists()) {
            throw new RuntimeException(
                'VatTypesSeeder: falta Country id='.self::SPAIN_COUNTRY_ID
                .' (España). Ejecuta GeographySeeder antes.',
            );
        }

        $path = database_path('data/vat-types/iva-types.json');

        if (! is_file($path)) {
            $this->command?->warn("VatTypesSeeder: {$path} no existe; se omite.");

            return;
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            $this->command?->warn("VatTypesSeeder: {$path} no contiene un array; se omite.");

            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];

        foreach ($decoded as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            $rows[] = [
                'id' => (int) $item['id'],
                'country_id' => self::SPAIN_COUNTRY_ID,
                'name' => (string) ($item['name'] ?? ''),
                'rate' => number_format((float) ($item['iva'] ?? 0), 2, '.', ''),
                'equivalence_surcharge' => array_key_exists('equivalence_surcharge', $item) && $item['equivalence_surcharge'] !== null
                    ? number_format((float) $item['equivalence_surcharge'], 2, '.', '')
                    : null,
                'status' => $this->mapStatus($item['status'] ?? null),
                'deleted_at' => $item['deleted_at'] ?? null,
                'created_at' => $this->normalizeTimestamp($item['created_at'] ?? null) ?? $now,
                'updated_at' => $this->normalizeTimestamp($item['updated_at'] ?? null) ?? $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('vat_types')->upsert($chunk, ['id'], self::COLUMNS);
        }

        $this->resetAutoIncrement();

        $this->command?->info('VatTypesSeeder: vat_types → '.count($rows).' filas (country_id='.self::SPAIN_COUNTRY_ID.').');
    }

    private function mapStatus(mixed $status): string
    {
        if ($status === 1 || $status === true || $status === '1' || $status === 'active') {
            return VatTypeStatus::Active->value;
        }

        return VatTypeStatus::Inactive->value;
    }

    private function normalizeTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $value = str_replace('T', ' ', rtrim($value, 'Z'));

        return preg_replace('/\.\d+$/', '', $value) ?? $value;
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table('vat_types')->max('id')) + 1;
        DB::statement("ALTER TABLE `vat_types` AUTO_INCREMENT = {$next}");
    }
}
