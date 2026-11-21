<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\AccountStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Plantillas mínimas de cuenta SaaS. Upsert por id; no borra filas ajenas;
 * restaura soft-delete de ids conocidos. Garantiza exactamente una default Active.
 * NO está en DatabaseSeeder; el Index no importa.
 *
 * Invocación: `php artisan db:seed --class=AccountsSeeder`
 */
class AccountsSeeder extends Seeder
{
    /** @var list<array{id: int, name: string, slug: string, description: string, rate: string, duration: int|null, is_default: bool}> */
    private const ACCOUNTS = [
        [
            'id' => 1,
            'name' => 'Básica',
            'slug' => 'basica',
            'description' => 'Plantilla por defecto del catálogo SaaS.',
            'rate' => '0.00',
            'duration' => 365,
            'is_default' => true,
        ],
        [
            'id' => 2,
            'name' => 'Profesional',
            'slug' => 'profesional',
            'description' => 'Plantilla con duración anual y tarifa base.',
            'rate' => '99.00',
            'duration' => 365,
            'is_default' => false,
        ],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::ACCOUNTS as $row) {
            $existing = DB::table('accounts')->where('id', $row['id'])->first();

            $payload = [
                'name' => $row['name'],
                'slug' => $row['slug'],
                'description' => $row['description'],
                'rate' => $row['rate'],
                'duration' => $row['duration'],
                'status' => AccountStatus::Active->value,
                'is_default' => $row['is_default'],
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if ($existing === null) {
                DB::table('accounts')->insert([
                    ...$payload,
                    'id' => $row['id'],
                    'created_at' => $now,
                ]);
            } else {
                DB::table('accounts')->where('id', $row['id'])->update($payload);
            }
        }

        // Garantiza una sola default (id 1) aunque hubiera filas ajenas marcadas.
        DB::table('accounts')->where('id', '!=', 1)->update(['is_default' => false]);
        DB::table('accounts')->where('id', 1)->update(['is_default' => true, 'deleted_at' => null]);

        $this->resetAutoIncrement();

        $this->command?->info('AccountsSeeder: accounts → '.count(self::ACCOUNTS).' filas canónicas.');
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table('accounts')->max('id')) + 1;
        DB::statement("ALTER TABLE `accounts` AUTO_INCREMENT = {$next}");
    }
}
