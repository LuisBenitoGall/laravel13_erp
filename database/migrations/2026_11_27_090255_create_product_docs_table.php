<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('path', 191);
            $table->string('original_name', 255);
            $table->string('mime', 127);
            $table->unsignedBigInteger('size_bytes');
            $table->boolean('public')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['product_id', 'path', 'deleted_at'], 'product_docs_product_path_deleted_unique');
            $table->index('product_id');
            $table->index('company_id');
            $table->index('public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_docs');
    }
};
