<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->string('description', 400)->nullable();
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->json('tags')->nullable();
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug')->unique();
            $table->string('icon', 40)->nullable();
            $table->string('excerpt', 400)->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('showcase_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug')->unique();
            $table->string('location', 120)->nullable();
            $table->string('client', 120)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('summary', 400)->nullable();
            $table->longText('description')->nullable();
            $table->string('category', 60)->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->json('gallery')->nullable();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author', 120);
            $table->string('role', 120)->nullable();
            $table->string('company', 120)->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar_path')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('showcase_projects');
        Schema::dropIfExists('services');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
