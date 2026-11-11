<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->string('position')->nullable()->after('company_id');
            $table->string('department')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropColumn(['position', 'department']);
        });
    }
};
