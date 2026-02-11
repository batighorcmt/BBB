<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name');
            $table->string('size')->nullable();
            $table->string('gsm')->nullable();
            $table->string('color')->nullable();
            
            // Costing Details (Snapshotted)
            $table->decimal('envelope_weight', 10, 3)->default(0);
            $table->decimal('envelope_price', 10, 2)->default(0);
            $table->decimal('loop_weight', 10, 3)->default(0);
            $table->decimal('loop_price', 10, 2)->default(0);
            $table->decimal('print_cost', 10, 2)->default(0);
            $table->decimal('sewing_cost', 10, 2)->default(0);
            
            $table->decimal('quantity', 12, 2);
            $table->decimal('price_per_piece', 10, 2);
            $table->decimal('total_price', 12, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
