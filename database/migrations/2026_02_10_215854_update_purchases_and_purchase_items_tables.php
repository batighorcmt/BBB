<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->after('purchase_no');
            $table->decimal('total_qty', 12, 2)->default(0)->after('purchase_date');
            $table->decimal('other_charges', 12, 2)->default(0)->after('subtotal');
            $table->string('discount_type')->default('fixed')->after('other_charges'); // fixed or percentage
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_type'); // Renaming/Using alongside 'discount' column if it exists, let's keep it simple and use existing 'discount' for final amount, add 'discount_rate' if needed. Existing table has 'discount'. Let's just add 'discount_type'.
            
            // Adjust existing default of 'status' if needed, here just adding new ones
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('item_discount', 12, 2)->default(0)->after('unit_price');
            $table->decimal('item_tax', 12, 2)->default(0)->after('item_discount');
            $table->decimal('net_unit_cost', 12, 2)->default(0)->after('item_tax'); // Unit cost after tax/discount logic if needed
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['reference_no', 'total_qty', 'other_charges', 'discount_type']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
             $table->dropColumn(['item_discount', 'item_tax', 'net_unit_cost']);
        });
    }
};
