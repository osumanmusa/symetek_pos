<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'ordered', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamps();
            
            $table->index('po_number');
            $table->index('order_date');
            $table->index('status');
            $table->index('supplier_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};