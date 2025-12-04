<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->dateTime('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['sale_id', 'payment_date']);
            $table->index('reference_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};