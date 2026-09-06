<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('label');
            $table->string('color', 32)->default('#64748b');
            $table->string('icon', 64)->default('box');
            $table->string('level', 32);
            $table->string('status', 32);
            $table->json('explanation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
