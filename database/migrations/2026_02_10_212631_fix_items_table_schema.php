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
            // Core Identity
            if (!Schema::hasColumn('items', 'code')) {
                $table->string('code')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('items', 'type')) {
                $table->enum('type', ['product', 'service'])->default('product')->after('code');
            }
            // Financials
            if (!Schema::hasColumn('items', 'price')) {
                 $table->decimal('price', 10, 2)->default(0)->after('type');
            }
            if (!Schema::hasColumn('items', 'cost')) {
                 $table->decimal('cost', 10, 2)->nullable()->default(0)->after('price');
            }
            // Inventory
            if (!Schema::hasColumn('items', 'stock_quantity')) {
                 $table->decimal('stock_quantity', 10, 2)->default(0)->after('cost');
            }
            if (!Schema::hasColumn('items', 'unit')) {
                 $table->string('unit')->nullable()->after('stock_quantity');
            }
            if (!Schema::hasColumn('items', 'description')) {
                 $table->text('description')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('items', 'status')) {
                 $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
            }

            // Specifications - Bag General
            if (!Schema::hasColumn('items', 'bag_type')) {
                $table->string('bag_type')->nullable();
            }
            if (!Schema::hasColumn('items', 'usage')) {
                $table->string('usage')->nullable();
            }
            if (!Schema::hasColumn('items', 'size')) {
                $table->string('size')->nullable();
            }
            if (!Schema::hasColumn('items', 'capacity')) {
                $table->string('capacity')->nullable();
            }

            // Material
            if (!Schema::hasColumn('items', 'gsm')) {
                $table->string('gsm')->nullable();
            }
            if (!Schema::hasColumn('items', 'fabric_color')) {
                $table->string('fabric_color')->nullable();
            }
            if (!Schema::hasColumn('items', 'fabric_quality')) {
                $table->string('fabric_quality')->nullable();
            }
            if (!Schema::hasColumn('items', 'lamination')) {
                $table->string('lamination')->nullable();
            }

            // Printing
            if (!Schema::hasColumn('items', 'print_type')) {
                $table->string('print_type')->nullable();
            }
            if (!Schema::hasColumn('items', 'print_color_count')) {
                $table->string('print_color_count')->nullable();
            }
            if (!Schema::hasColumn('items', 'print_sides')) {
                $table->string('print_sides')->nullable();
            }
            if (!Schema::hasColumn('items', 'brand_name')) {
                $table->string('brand_name')->nullable();
            }
            if (!Schema::hasColumn('items', 'design_file')) {
                $table->string('design_file')->nullable();
            }

            // Finishing
            if (!Schema::hasColumn('items', 'handle_type')) {
                $table->string('handle_type')->nullable();
            }
            if (!Schema::hasColumn('items', 'handle_color')) {
                $table->string('handle_color')->nullable();
            }
            if (!Schema::hasColumn('items', 'stitching_type')) {
                $table->string('stitching_type')->nullable();
            }
            if (!Schema::hasColumn('items', 'bottom_finish')) {
                $table->string('bottom_finish')->nullable();
            }

            // Soft Deletes
            if (!Schema::hasColumn('items', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation for this fix migration as it fixes missing columns.
        // Dropping these logic depends on whether they were created mistakenly.
        // Safest is to do nothing or drop specific columns if absolutely needed.
    }
};
