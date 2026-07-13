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
        Schema::create('payslip_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['allowance', 'deduction']);
            $table->foreignId('employee_loan_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_components');
    }
};
