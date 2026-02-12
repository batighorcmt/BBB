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
        Schema::table('productions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'converted', 'working', 'production_ready', 'completed', 'cancelled', 'delivered'])->default('pending')->change();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'converted', 'working', 'production_ready', 'completed', 'cancelled', 'delivered'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'working', 'completed', 'cancelled'])->default('pending')->change();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'converted'])->default('pending')->change();
        });
    }
};
