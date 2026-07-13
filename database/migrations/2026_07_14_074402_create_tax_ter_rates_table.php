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
        Schema::create('tax_ter_rates', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 10); // A, B, C, PASAL17
            $table->integer('no_lapisan');
            $table->decimal('batas_bawah', 15, 2)->default(0);
            $table->decimal('batas_atas', 15, 2)->nullable();
            $table->decimal('tarif', 5, 4); // percentage decimal e.g. 0.0025 for 0.25%
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_ter_rates');
    }
};
