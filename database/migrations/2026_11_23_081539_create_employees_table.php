<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('contract_type_id')->constrained('contract_types')->restrictOnDelete();
            $table->foreignId('workplace_id')->nullable()->constrained('workplaces')->nullOnDelete();
            $table->string('job_title')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('weekly_hours', 5, 2)->default(0);
            $table->boolean('time_control')->default(true);
            $table->decimal('year_salary', 12, 2)->nullable();
            $table->string('status', 32);
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'user_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'contract_type_id']);
            $table->index(['company_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
