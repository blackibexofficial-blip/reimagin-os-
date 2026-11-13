<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->string('description', 500)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 60)->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->string('short_description', 300)->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('price_minor')->default(0);
            $table->bigInteger('compare_at_price_minor')->nullable();
            $table->bigInteger('cost_minor')->nullable();
            $table->char('currency', 3)->default('PKR');
            $table->boolean('is_quote_only')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(3);
            $table->string('material', 120)->nullable();
            $table->string('finish', 120)->nullable();
            $table->string('color', 60)->nullable();
            $table->string('dimensions', 120)->nullable();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('depth_cm', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_active']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'media_id']);
        });

        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('sku', 60)->unique();
            $table->bigInteger('price_minor')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_relations');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
