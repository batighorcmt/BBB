<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->timestamps();
            
            $table->unique('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
