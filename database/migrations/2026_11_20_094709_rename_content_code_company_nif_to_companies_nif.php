<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = DB::table('contents')->where('code', 'company-nif')->first();
        if ($legacy === null) {
            return;
        }

        $targetExists = DB::table('contents')->where('code', 'companies.nif')->exists();
        if ($targetExists) {
            if ($legacy->deleted_at === null) {
                DB::table('contents')->where('id', $legacy->id)->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return;
        }

        DB::table('contents')->where('id', $legacy->id)->update([
            'code' => 'companies.nif',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $current = DB::table('contents')->where('code', 'companies.nif')->first();
        if ($current === null) {
            return;
        }

        $legacyExists = DB::table('contents')->where('code', 'company-nif')->exists();
        if ($legacyExists) {
            return;
        }

        DB::table('contents')->where('id', $current->id)->update([
            'code' => 'company-nif',
            'updated_at' => now(),
        ]);
    }
};
