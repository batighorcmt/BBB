<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to ensure the change happens regardless of Doctrine DBAL presence
        DB::statement("ALTER TABLE items MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'product'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM if needed, though VARCHAR is safer
        DB::statement("ALTER TABLE items MODIFY COLUMN type ENUM('product', 'service') NOT NULL DEFAULT 'product'");
    }
};
