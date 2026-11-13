<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('contact_name', 120)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* The stock ledger. stock_quantity on products is a cached balance that
           can always be rebuilt from this table via StockService. */
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('type', 30)->default('adjustment')->index();
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->string('reference', 60)->nullable();
            $table->string('reason', 180)->nullable();
            $table->nullableMorphs('source');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->bigInteger('total_minor')->default(0);
            $table->char('currency', 3)->default('PKR');
            $table->date('expected_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 200);
            $table->integer('quantity')->default(1);
            $table->integer('received_quantity')->default(0);
            $table->bigInteger('unit_cost_minor')->default(0);
            $table->bigInteger('line_total_minor')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->string('name', 180);
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('planning')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->bigInteger('budget_minor')->default(0);
            $table->string('site_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('position')->default(0);
            $table->date('started_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('version', 20)->default('v1');
            $table->string('status', 20)->default('draft');
            $table->foreignId('media_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('type', 30)->default('showroom_visit');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('requested')->index();
            $table->string('location', 180)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            /* One staff member cannot hold two appointments at the same instant. */
            $table->unique(['staff_id', 'starts_at']);
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('designs');
        Schema::dropIfExists('project_stages');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('suppliers');
    }
};
