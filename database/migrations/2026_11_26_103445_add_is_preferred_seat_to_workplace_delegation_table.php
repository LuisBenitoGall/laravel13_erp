<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workplace_delegation', function (Blueprint $table): void {
            $table->boolean('is_preferred_seat')->default(false)->after('is_preferred');
            $table->index(['company_id', 'is_preferred_seat'], 'workplace_delegation_company_seat_index');
        });
    }

    public function down(): void
    {
        Schema::table('workplace_delegation', function (Blueprint $table): void {
            $table->dropIndex('workplace_delegation_company_seat_index');
            $table->dropColumn('is_preferred_seat');
        });
    }
};
