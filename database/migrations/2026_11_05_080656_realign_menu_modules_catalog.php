<?php

use App\Domains\Admin\Actions\RealignMenuCatalog;
use App\Domains\Admin\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('modules')) {
            return;
        }

        $needsRealign = Module::withTrashed()
            ->whereIn('slug', ['admin', 'workspace'])
            ->exists();

        if (! $needsRealign) {
            return;
        }

        app(RealignMenuCatalog::class)->handle();
    }

    public function down(): void
    {
        // Irreversible one-shot realign (admin/workspace → PDF modules).
    }
};
