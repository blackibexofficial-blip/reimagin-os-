<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('path')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->string('disk', 40)->default('public');
            $table->string('path');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 180)->nullable();
            $table->string('caption', 300)->nullable();
            $table->string('focal_point', 20)->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->index('mime_type');
        });

        /* Polymorphic so deleting a product or page can cascade its references. */
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->morphs('usable');
            $table->string('field', 40)->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'usable_type', 'usable_id', 'field'], 'media_usages_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_folders');
    }
};
