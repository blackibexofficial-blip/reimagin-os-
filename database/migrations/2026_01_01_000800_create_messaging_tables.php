<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('channel', 20)->default('web');
            $table->string('status', 20)->default('open')->index();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name', 120)->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 40)->nullable();
            $table->string('external_id', 120)->nullable();
            $table->string('subject', 180)->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->unsignedInteger('unread_for_staff')->default(0);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('author_type', 20)->default('visitor');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->json('meta')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });

        Schema::create('ai_agent_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model', 60)->nullable();
            $table->string('intent', 60)->nullable();
            $table->json('prompt_context')->nullable();
            $table->text('reply')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->boolean('escalated')->default(false);
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_runs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
