<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote N:M Workplace de la matriz ↔ Company delegación (hija de la matriz).
     * `company_id` = delegación (no es el tenant del workplace); sin BelongsToCompany.
     */
    public function up(): void
    {
        Schema::create('workplace_delegation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workplace_id')->constrained('workplaces')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();

            $table->unique(['workplace_id', 'company_id']);
            $table->index(['company_id', 'workplace_id']);
            $table->index(['workplace_id', 'is_preferred']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workplace_delegation');
    }
};
