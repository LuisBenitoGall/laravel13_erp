<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('tradename')->nullable();
            $table->string('lei')->nullable();
            $table->string('eu_code')->nullable();
            $table->string('supervisor_code')->nullable();
            $table->char('swift', 11)->nullable();
            $table->string('swift_source', 30)->nullable();
            $table->timestamp('swift_updated_at')->nullable();
            $table->string('status', 32);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('status');
            $table->index('eu_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
