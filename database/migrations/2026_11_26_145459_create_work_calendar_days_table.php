<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_calendar_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_calendar_id')->constrained('work_calendars')->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->foreignId('day_type_id')->constrained('day_types')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['work_calendar_id', 'date']);
            $table->index('day_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_calendar_days');
    }
};
