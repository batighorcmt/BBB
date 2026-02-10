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
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->enum('type', ['product', 'service'])->default('product');
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('cost', 10, 2)->nullable()->default(0); // Cost price
                $table->decimal('stock_quantity', 10, 2)->default(0);
                $table->string('unit')->nullable(); // e.g., pcs, kg, hr
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
