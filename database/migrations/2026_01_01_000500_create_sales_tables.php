<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('revision')->default(1);
            $table->json('customer_snapshot')->nullable();
            $table->bigInteger('subtotal_minor')->default(0);
            $table->bigInteger('discount_minor')->default(0);
            $table->string('discount_type', 10)->default('fixed');
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->bigInteger('tax_minor')->default(0);
            $table->bigInteger('delivery_minor')->default(0);
            $table->bigInteger('total_minor')->default(0);
            $table->char('currency', 3)->default('PKR');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 300);
            $table->json('customisation')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->bigInteger('unit_price_minor')->default(0);
            $table->bigInteger('line_total_minor')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision');
            $table->json('snapshot')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 180)->nullable();
            $table->timestamps();

            $table->unique(['quotation_id', 'revision']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            /* Unique: a quotation can only ever produce one order. */
            $table->foreignId('quotation_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->bigInteger('subtotal_minor')->default(0);
            $table->bigInteger('discount_minor')->default(0);
            $table->bigInteger('tax_minor')->default(0);
            $table->bigInteger('delivery_minor')->default(0);
            $table->bigInteger('total_minor')->default(0);
            $table->bigInteger('paid_minor')->default(0);
            $table->char('currency', 3)->default('PKR');
            $table->text('notes')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('cancellation_reason', 300)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 180);
            $table->string('sku', 60)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->bigInteger('unit_price_minor')->default(0);
            $table->bigInteger('line_total_minor')->default(0);
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 60);
            $table->string('method', 30)->default('cash');
            $table->string('type', 20)->default('advance');
            $table->string('status', 20)->default('confirmed');
            $table->bigInteger('amount_minor');
            $table->char('currency', 3)->default('PKR');
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['order_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('quotation_revisions');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
