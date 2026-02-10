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
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'bag_type')) {
            Schema::table('items', function (Blueprint $table) {
                // Brand (Moved to top to allow other columns to be placed after it)
                $table->string('brand_name')->nullable()->after('description'); // Logo/Brand

                // Basic Info
                $table->string('bag_type')->nullable()->after('brand_name'); // e.g., D-cut, W-cut
                $table->string('usage')->nullable()->after('bag_type'); // e.g., Shopping, Grocery
                $table->string('size')->nullable()->after('usage'); // Height x Width x Gusset
                $table->string('capacity')->nullable()->after('size'); // In Kg

                // Material
                $table->string('gsm')->nullable()->after('capacity'); // e.g., 60, 70
                $table->string('fabric_color')->nullable()->after('gsm');
                $table->string('fabric_quality')->nullable()->after('fabric_color'); // Virgin / Recycled
                $table->string('lamination')->nullable()->after('fabric_quality'); // Matte / Glossy / None

                // Printing
                $table->string('print_type')->nullable()->after('lamination'); // Screen / Offset
                $table->string('print_color_count')->nullable()->after('print_type'); // 1 color, 2 color
                $table->string('print_sides')->nullable()->after('print_color_count'); // Single / Double
                $table->string('design_file')->nullable()->after('print_sides'); // File path

                // Finishing
                $table->string('handle_type')->nullable()->after('design_file'); // Loop, D-cut
                $table->string('handle_color')->nullable()->after('handle_type');
                $table->string('stitching_type')->nullable()->after('handle_color'); // Stitching / Heat Seal
                $table->string('bottom_finish')->nullable()->after('stitching_type'); // Box Bottom / Stitch
            });
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'bag_type', 'usage', 'size', 'capacity',
                'gsm', 'fabric_color', 'fabric_quality', 'lamination',
                'print_type', 'print_color_count', 'print_sides', 'design_file', 'brand_name',
                'handle_type', 'handle_color', 'stitching_type', 'bottom_finish'
            ]);
        });
    }
};
