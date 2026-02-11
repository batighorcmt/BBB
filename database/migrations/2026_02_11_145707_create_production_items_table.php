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
        Schema::create('production_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name');
            $table->string('size')->nullable();
            
            // Production Metrics
            $table->decimal('quantity', 10, 2)->default(0);
            
            // Costing Factors
            $table->decimal('envelope_weight', 10, 3)->default(0); // kg
            $table->decimal('envelope_price', 10, 2)->default(0); // per kg cost? or total? "envelope_price * weight" implies price per unit weight
            $table->decimal('loop_weight', 10, 3)->default(0);
            $table->decimal('loop_price', 10, 2)->default(0);
            $table->decimal('print_cost', 10, 2)->default(0); // per piece or total? Plan says "PrintPrice * Qty" so per piece
            $table->decimal('sewing_cost', 10, 2)->default(0); // per piece
            
            $table->decimal('price_per_piece', 10, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);

            // Wastage
            $table->decimal('wastage_kg', 10, 3)->default(0);
            $table->decimal('wastage_piece', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_items');
    }
};
