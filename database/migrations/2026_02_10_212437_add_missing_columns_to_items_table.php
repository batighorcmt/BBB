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
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->default(0)->after('price');
            }
            if (!Schema::hasColumn('items', 'stock_quantity')) {
                $table->decimal('stock_quantity', 10, 2)->default(0)->after('cost');
            }
            if (!Schema::hasColumn('items', 'unit')) {
                $table->string('unit')->nullable()->after('stock_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['cost', 'stock_quantity', 'unit']);
        });
    }
};
