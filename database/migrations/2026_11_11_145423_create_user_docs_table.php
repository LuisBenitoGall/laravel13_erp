<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('path', 191);
            $table->string('original_name', 255);
            $table->string('mime', 127);
            $table->unsignedBigInteger('size_bytes');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_id', 'path', 'deleted_at'], 'user_docs_user_path_deleted_unique');
            $table->index('user_id', 'user_docs_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_docs');
    }
};
