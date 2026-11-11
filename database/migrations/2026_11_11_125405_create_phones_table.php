<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->morphs('phoneable');
            $table->string('e164', 30);
            $table->foreignId('country_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('ext', 10)->nullable();
            $table->string('type', 32)->default('mobile');
            $table->string('label', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_whatsapp')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['phoneable_type', 'phoneable_id', 'e164'], 'phones_owner_e164_unique');
            $table->index(['phoneable_type', 'phoneable_id', 'is_primary'], 'phones_owner_primary_idx');
            $table->index(['phoneable_type', 'phoneable_id', 'type'], 'phones_owner_type_idx');
            $table->index('e164', 'phones_e164_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
