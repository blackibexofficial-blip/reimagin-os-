<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->string('excerpt', 500)->nullable();
            $table->json('content')->nullable();
            $table->json('published_content')->nullable();
            $table->json('meta')->nullable();
            $table->string('template', 40)->default('default');
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('is_homepage')->default(false)->index();
            $table->boolean('show_in_navigation')->default(false);
            $table->unsignedInteger('navigation_order')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('revision')->default(1);
            $table->json('content')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->string('label', 60)->default('Manual save');
            $table->string('reason', 180)->nullable();
            $table->timestamps();

            $table->index(['page_id', 'revision']);
        });

        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('location', 30)->default('header')->index();
            $table->string('label', 80);
            $table->string('url')->nullable();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('open_in_new_tab')->default(false);
            $table->timestamps();

            $table->index(['location', 'position']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40)->default('general');
            $table->string('key', 80)->unique();
            $table->json('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
    }
};
