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
        Schema::create('bpjs_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->decimal('jht_amount', 15, 2)->default(0);
            $table->decimal('jp_amount', 15, 2)->default(0);
            $table->decimal('jkk_amount', 15, 2)->default(0);
            $table->decimal('jkm_amount', 15, 2)->default(0);
            $table->decimal('kesehatan_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpjs_records');
    }
};
