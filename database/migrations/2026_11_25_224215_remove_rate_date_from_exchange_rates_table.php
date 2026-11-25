<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Par único sin historial: purga prueba, drop rate_date, unique (from, to).
 * En MySQL hay que soltar FKs antes de dropear el unique compuesto que las indexa.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('exchange_rates')->delete();

        Schema::table('exchange_rates', function (Blueprint $table): void {
            $table->dropForeign(['from_currency_id']);
            $table->dropForeign(['to_currency_id']);
        });

        Schema::table('exchange_rates', function (Blueprint $table): void {
            $table->dropUnique('exchange_rates_pair_date_unique');
            $table->dropIndex(['rate_date']);
            $table->dropColumn('rate_date');
        });

        Schema::table('exchange_rates', function (Blueprint $table): void {
            $table->unique(
                ['from_currency_id', 'to_currency_id'],
                'exchange_rates_pair_unique',
            );
            $table->foreign('from_currency_id')->references('id')->on('currencies')->restrictOnDelete();
            $table->foreign('to_currency_id')->references('id')->on('currencies')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table): void {
            $table->dropForeign(['from_currency_id']);
            $table->dropForeign(['to_currency_id']);
            $table->dropUnique('exchange_rates_pair_unique');
        });

        Schema::table('exchange_rates', function (Blueprint $table): void {
            $table->date('rate_date')->nullable()->after('rate');
            $table->unique(
                ['from_currency_id', 'to_currency_id', 'rate_date'],
                'exchange_rates_pair_date_unique',
            );
            $table->index('rate_date');
            $table->foreign('from_currency_id')->references('id')->on('currencies')->restrictOnDelete();
            $table->foreign('to_currency_id')->references('id')->on('currencies')->restrictOnDelete();
        });
    }
};
