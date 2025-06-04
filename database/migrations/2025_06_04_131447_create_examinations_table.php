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
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checkup_id')->constrained()->cascadeOnDelete();

            // Data fisik universal
            $table->float('weight'); // BB (kg)
            $table->float('height'); // TB/PB (cm)

            // Data spesifik kategori
            $table->float('arm_circumference')->nullable(); // Lingkar lengan
            $table->float('head_circumference')->nullable(); // Balita
            $table->float('abdominal_circumference')->nullable(); // Remaja/dewasa/lansia/ibu-hamil
            $table->string('tension')->nullable(); // Tensi (contoh: "120/80")
            $table->float('uric_acid')->nullable(); // Asam urat
            $table->float('blood_sugar')->nullable(); // Gula darah
            $table->float('cholesterol')->nullable(); // Kolesterol
            $table->integer('gestational_week')->nullable(); //usia kehamilan

            // Hasil analisis
            $table->string('weight_status')->nullable(); // Status gizi
            $table->text('recommendation')->nullable(); // Rekomendasi

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
