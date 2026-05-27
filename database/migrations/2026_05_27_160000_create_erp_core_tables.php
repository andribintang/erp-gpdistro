<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
            $table->index(['name', 'parent_id']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 80)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('product_type', ['apparel', 'spare_part', 'custom_service'])->index();
            $table->decimal('price', 16, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->index(['brand_id', 'category_id']);
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('stock_on_hand')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->index(['name', 'phone']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->enum('channel', ['erp', 'web', 'marketplace'])->default('erp')->index();
            $table->enum('status', ['draft', 'pending', 'paid', 'processing', 'completed', 'cancelled'])->default('pending')->index();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('shipping_cost', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('grand_total', 16, 2)->default(0);
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->integer('qty');
            $table->decimal('price', 16, 2);
            $table->decimal('subtotal', 16, 2);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->enum('method', ['manual_transfer', 'midtrans', 'xendit', 'qris', 'virtual_account'])->default('manual_transfer');
            $table->enum('status', ['pending', 'verified', 'failed', 'refunded'])->default('pending')->index();
            $table->decimal('amount', 16, 2);
            $table->string('proof_path')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_number')->unique();
            $table->string('courier')->index();
            $table->string('service')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->enum('status', ['pending', 'packed', 'shipped', 'delivered', 'returned'])->default('pending')->index();
            $table->decimal('shipping_cost', 16, 2)->default(0);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('production_number')->unique();
            $table->enum('type', ['custom_jersey', 'uniform', 'other'])->default('custom_jersey');
            $table->enum('status', ['design_review', 'approved', 'in_progress', 'qc', 'ready_to_ship', 'done'])->default('design_review')->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->enum('status', ['present', 'late', 'leave', 'absent'])->default('present');
            $table->timestamps();
            $table->unique(['user_id', 'attendance_date']);
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 20)->index();
            $table->decimal('basic_salary', 16, 2)->default(0);
            $table->decimal('allowance', 16, 2)->default(0);
            $table->decimal('deduction', 16, 2)->default(0);
            $table->decimal('net_salary', 16, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft')->index();
            $table->timestamps();
            $table->unique(['user_id', 'period']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('expense_number')->unique();
            $table->string('category')->index();
            $table->string('description');
            $table->decimal('amount', 16, 2);
            $table->date('expense_date')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('warehouses');
    }
};
