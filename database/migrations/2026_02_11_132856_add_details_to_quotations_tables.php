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
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('advance_amount', 12, 2)->default(0)->after('total');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('size')->nullable()->after('description');
            $table->string('color')->nullable()->after('size');
            $table->string('gsm')->nullable()->after('color');
            $table->string('print_color')->nullable()->after('gsm');
            $table->string('print_side')->nullable()->after('print_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['size', 'color', 'gsm', 'print_color', 'print_side']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('advance_amount');
        });
    }
};
