<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code')->unique();
            $table->string('kind', 32);
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->json('excerpt');
            $table->json('content');
            $table->string('referrer')->nullable();
            $table->string('video')->nullable();
            $table->string('status', 32);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kind', 'status']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
