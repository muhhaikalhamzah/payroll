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
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('request_date');
            $table->text('reason');
            $table->decimal('total_amount', 15, 2);
            $table->integer('requested_tenor_months');
            $table->decimal('monthly_installment', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->enum('status', ['DRAFT', 'PENDING_FINANCE', 'APPROVED', 'REJECTED', 'DISBURSED', 'COMPLETED'])->default('DRAFT');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
