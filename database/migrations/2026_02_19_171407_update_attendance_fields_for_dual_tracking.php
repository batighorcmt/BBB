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
            $table->renameColumn('photo_path', 'check_in_photo');
            $table->renameColumn('latitude', 'check_in_latitude');
            $table->renameColumn('longitude', 'check_in_longitude');
            $table->renameColumn('location_address', 'check_in_address');

            $table->string('check_out_photo')->after('check_in_address')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->after('check_out_photo')->nullable();
            $table->decimal('check_out_longitude', 11, 8)->after('check_out_latitude')->nullable();
            $table->text('check_out_address')->after('check_out_longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->renameColumn('check_in_photo', 'photo_path');
            $table->renameColumn('check_in_latitude', 'latitude');
            $table->renameColumn('check_in_longitude', 'longitude');
            $table->renameColumn('check_in_address', 'location_address');

            $table->dropColumn(['check_out_photo', 'check_out_latitude', 'check_out_longitude', 'check_out_address']);
        });
    }
};
