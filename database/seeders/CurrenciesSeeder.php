<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\CurrencyStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Monedas globales portadas de v1 (Viudavila) con IDs preservados (EUR=2, USD=5, GBP=67, CHF=95…).
 *
 * Fuente: `database/data/currencies/currencies.json` (array de filas con `id`). Idempotente:
 * upsert por `id`. Status por código: EUR, USD, GBP y CHF → `active`; el resto → `inactive`.
 * Símbolo null → mapa v1 (EUR→€, USD→$, AUD→$, CAD→$); el resto se conserva tal cual viene.
 * Si el fichero no existe se omite con aviso. Resetea AUTO_INCREMENT (MySQL). No re-sembrar
 * si ya hay FKs consumidoras. NO está en `DatabaseSeeder` y el Index nunca importa (D7).
 *
 * Invocación: `php artisan db:seed --class=CurrenciesSeeder`
 */
class CurrenciesSeeder extends Seeder
{
    private const CHUNK = 500;

    /** @var list<string> */
    private const ACTIVE_CODES = ['EUR', 'USD', 'GBP', 'CHF'];

    /** @var array<string, string> */
    private const SYMBOLS = [
        'EUR' => '€',
        'USD' => '$',
        'AUD' => '$',
        'CAD' => '$',
    ];

    private const COLUMNS = ['name', 'slug', 'code', 'number', 'symbol', 'status', 'deleted_at', 'created_at', 'updated_at'];

    public function run(): void
    {
        $path = database_path('data/currencies/currencies.json');

        if (! is_file($path)) {
            $this->command?->warn("CurrenciesSeeder: {$path} no existe; se omite.");

            return;
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            $this->command?->warn("CurrenciesSeeder: {$path} no contiene un array; se omite.");

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

            $code = is_string($row['code']) ? strtoupper($row['code']) : null;

            $row['status'] = $code !== null && in_array($code, self::ACTIVE_CODES, true)
                ? CurrencyStatus::Active->value
                : CurrencyStatus::Inactive->value;

            if (($row['symbol'] === null || $row['symbol'] === '') && $code !== null) {
                $row['symbol'] = self::SYMBOLS[$code] ?? null;
            }

            $row['created_at'] ??= $now;
            $row['updated_at'] ??= $now;

            $rows[] = $row;
        }

        $columns = self::COLUMNS;

        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            DB::table('currencies')->upsert($chunk, ['id'], $columns);
        }

        $this->resetAutoIncrement();

        $this->command?->info('CurrenciesSeeder: currencies → '.count($rows).' filas.');
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table('currencies')->max('id')) + 1;
        DB::statement("ALTER TABLE `currencies` AUTO_INCREMENT = {$next}");
    }
}
