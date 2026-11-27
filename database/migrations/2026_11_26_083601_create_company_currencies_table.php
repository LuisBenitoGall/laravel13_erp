<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_currencies');
    }
};
