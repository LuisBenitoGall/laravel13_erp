<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\PaymentMethodStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Métodos de pago canónicos (ids 1–12), name ES/EN/CA, status Active.
 * Upsert por id; no borra filas ajenas; restaura soft-delete de 1–12.
 * NO está en DatabaseSeeder; el Index no importa.
 *
 * Invocación: `php artisan db:seed --class=PaymentMethodsSeeder`
 */
class PaymentMethodsSeeder extends Seeder
{
    /** @var list<array{id: int, name: array{es: string, en: string, ca: string}}> */
    private const METHODS = [
        [
            'id' => 1,
            'name' => [
                'es' => 'Contado en efectivo',
                'en' => 'Cash',
                'ca' => 'Efectiu',
            ],
        ],
        [
            'id' => 2,
            'name' => [
                'es' => 'Cheque/pagaré',
                'en' => 'Cheque/promissory note',
                'ca' => 'Xec/pagaré',
            ],
        ],
        [
            'id' => 3,
            'name' => [
                'es' => 'Confirming',
                'en' => 'Confirming',
                'ca' => 'Confirming',
            ],
        ],
        [
            'id' => 4,
            'name' => [
                'es' => 'Domiciliación bancaria',
                'en' => 'Direct debit',
                'ca' => 'Domiciliació bancària',
            ],
        ],
        [
            'id' => 5,
            'name' => [
                'es' => 'Recibo',
                'en' => 'Receipt',
                'ca' => 'Rebut',
            ],
        ],
        [
            'id' => 6,
            'name' => [
                'es' => 'Talón',
                'en' => 'Bank draft',
                'ca' => 'Taló',
            ],
        ],
        [
            'id' => 7,
            'name' => [
                'es' => 'Transferencia',
                'en' => 'Bank transfer',
                'ca' => 'Transferència',
            ],
        ],
        [
            'id' => 8,
            'name' => [
                'es' => 'Tarjeta de crédito',
                'en' => 'Credit card',
                'ca' => 'Targeta de crèdit',
            ],
        ],
        [
            'id' => 9,
            'name' => [
                'es' => 'A concretar',
                'en' => 'To be confirmed',
                'ca' => 'A concretar',
            ],
        ],
        [
            'id' => 10,
            'name' => [
                'es' => 'Abono de tarjetas en banco',
                'en' => 'Card settlement at bank',
                'ca' => 'Abonament de targetes al banc',
            ],
        ],
        [
            'id' => 11,
            'name' => [
                'es' => 'Compensación',
                'en' => 'Compensation',
                'ca' => 'Compensació',
            ],
        ],
        [
            'id' => 12,
            'name' => [
                'es' => 'Bizum',
                'en' => 'Bizum',
                'ca' => 'Bizum',
            ],
        ],
    ];

    private const UPSERT_COLUMNS = [
        'name',
        'status',
        'deleted_at',
        'updated_at',
    ];

    public function run(): void
    {
        $now = now()->toDateTimeString();
        $rows = [];

        foreach (self::METHODS as $method) {
            $rows[] = [
                'id' => $method['id'],
                'name' => json_encode($method['name'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'status' => PaymentMethodStatus::Active->value,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('payment_methods')->upsert(
            $rows,
            ['id'],
            self::UPSERT_COLUMNS,
        );

        $this->resetAutoIncrement();

        $this->command?->info('PaymentMethodsSeeder: payment_methods → '.count($rows).' filas.');
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $next = ((int) DB::table('payment_methods')->max('id')) + 1;
        DB::statement("ALTER TABLE `payment_methods` AUTO_INCREMENT = {$next}");
    }
}
