<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('company', 120)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->char('country', 2)->default('PK');
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('lifetime_value_minor')->default(0);
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('phone');
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('company', 120)->nullable();
            $table->string('source', 30)->default('website')->index();
            $table->string('status', 20)->default('new')->index();
            $table->string('priority', 10)->default('normal');
            $table->unsignedTinyInteger('score')->default(0);
            $table->bigInteger('estimated_value_minor')->default(0);
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->string('lost_reason', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->string('type', 30)->default('note');
            $table->string('title', 180);
            $table->text('body')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->morphs('taskable');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('open');
            $table->string('priority', 10)->default('normal');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('customers');
    }
};
