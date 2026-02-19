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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('photo_path')->after('status')->nullable();
            $table->decimal('latitude', 10, 8)->after('photo_path')->nullable();
            $table->decimal('longitude', 11, 8)->after('latitude')->nullable();
            $table->text('location_address')->after('longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'latitude', 'longitude', 'location_address']);
        });
    }
};
