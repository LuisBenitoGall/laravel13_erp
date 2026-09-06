<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->id();
            // Sin FK a companies: la tabla de negocio aún no existe (Non-Goal).
            $table->unsignedBigInteger('company_id');
            $table->string('doc_type', 64);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'doc_type']);
            $table->index(['company_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_numbers');
    }
};
