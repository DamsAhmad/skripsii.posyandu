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
            $table->float('weight');
            $table->float('height');
            $table->float('arm_circumference')->nullable();
            $table->float('head_circumference')->nullable();
            $table->float('abdominal_circumference')->nullable();
            $table->string('tension')->nullable();
            $table->float('uric_acid')->nullable();
            $table->float('blood_sugar')->nullable();
            $table->float('cholesterol')->nullable();
            $table->integer('gestational_week')->nullable();
            $table->string('weight_status')->nullable();
            $table->float('z_score')->nullable();
            $table->float('anthropometric_value')->nullable();
            $table->text('recommendation')->nullable();
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
