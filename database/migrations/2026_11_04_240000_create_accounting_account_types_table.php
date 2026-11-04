<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_account_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('autoreference')->default(0);
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->char('mode', 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('autoreference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_account_types');
    }
};
