<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->date('transaction_date');
            $table->enum('type', ['income', 'expense']); // Income or Expense
            $table->string('category'); // Salary, Utility, Sales, etc.
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank', 'mobile_banking', 'cheque'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
