<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create warehouses table FIRST
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('code');
            $table->index('is_active');
        });
        
        // THEN create inventory_transactions table
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type'); // purchase, sale, adjustment, return, damage, transfer
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('reference_type')->nullable(); // purchase_order, sale, adjustment, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference record
            $table->string('reference_number')->nullable(); // PO number, invoice number, etc.
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data like location, batch number, expiry date
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('transaction_type');
            $table->index('reference_type');
            $table->index('reference_id');
            $table->index('transaction_date');
        });
        
        // Create inventory levels table LAST (it references both products and warehouses)
        Schema::create('inventory_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->decimal('quantity_committed', 10, 2)->default(0);
            $table->decimal('quantity_available', 10, 2)->virtualAs('quantity_on_hand - quantity_committed');
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->decimal('total_value', 10, 2)->virtualAs('quantity_on_hand * average_cost');
            $table->date('last_count_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['product_id', 'warehouse_id']);
            $table->index('quantity_available');
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        // Drop in reverse order
        Schema::dropIfExists('inventory_levels');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('warehouses');
    }
};
