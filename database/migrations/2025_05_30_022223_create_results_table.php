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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->float('weight');
            $table->float('height')->nullable();
            $table->float('head_circum')->nullable();
            $table->float('hand_circum')->nullable();
            $table->float('waist_circum')->nullable();
            $table->float('z_score')->nullable();
            $table->string('nutrition_status')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('checkup_id')->constrained();
            $table->foreignId('member_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
