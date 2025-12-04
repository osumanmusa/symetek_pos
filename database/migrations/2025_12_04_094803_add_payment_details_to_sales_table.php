<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add these columns if they don't exist
            if (!Schema::hasColumn('sales', 'payment_date')) {
                $table->dateTime('payment_date')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('sales', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('sales', 'received_by')) {
                $table->foreignId('received_by')->nullable()->constrained('users')->after('payment_reference')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_date', 'payment_reference', 'received_by']);
        });
    }
};