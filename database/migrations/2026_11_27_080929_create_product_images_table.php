<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('image', 191);
            $table->boolean('featured')->default(false);
            $table->boolean('public')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['product_id', 'image', 'deleted_at'], 'product_images_product_image_deleted_unique');
            $table->index(['company_id', 'product_id']);
            $table->index('public');
            $table->index('deleted_at');
        });

        try {
            DB::statement('
                ALTER TABLE product_images
                ADD COLUMN featured_product_id BIGINT UNSIGNED
                GENERATED ALWAYS AS (CASE WHEN featured = 1 THEN product_id ELSE NULL END) STORED
            ');
            DB::statement('
                ALTER TABLE product_images
                ADD UNIQUE KEY product_images_one_featured_per_product (featured_product_id)
            ');
        } catch (Throwable) {
            // SQLite / engines without generated columns: Action enforces uniqueness.
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE product_images DROP INDEX product_images_one_featured_per_product');
        } catch (Throwable) {
        }

        try {
            DB::statement('ALTER TABLE product_images DROP COLUMN featured_product_id');
        } catch (Throwable) {
        }

        Schema::dropIfExists('product_images');
    }
};
