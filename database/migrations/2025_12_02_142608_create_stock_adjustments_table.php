<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('adjustment_date');
            $table->enum('adjustment_type', ['add', 'remove', 'set', 'damage', 'return', 'transfer', 'correction']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('previous_quantity', 10, 2);
            $table->decimal('new_quantity', 10, 2);
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->foreignId('reference_id')->nullable()->comment('Reference to PO, Sale, Transfer, etc.');
            $table->string('reference_type')->nullable();
            $table->timestamps();
            
            $table->index('adjustment_number');
            $table->index('adjustment_date');
            $table->index('product_id');
            $table->index('user_id');
            $table->index('adjustment_type');
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};