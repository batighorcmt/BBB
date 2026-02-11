<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'current_balance')) {
                $table->decimal('current_balance', 12, 2)->default(0)->after('opening_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
             if (Schema::hasColumn('customers', 'current_balance')) {
                $table->dropColumn('current_balance');
            }
        });
    }
};
