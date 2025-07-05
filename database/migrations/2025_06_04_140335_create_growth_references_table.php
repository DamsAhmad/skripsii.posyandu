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
        Schema::create('growth_references', function (Blueprint $table) {
            $table->id();
            $table->string('indicator');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->integer('age_months');
            $table->float('sd_minus_3')->nullable();
            $table->float('sd_minus_2')->nullable();
            $table->float('sd_minus_1')->nullable();
            $table->float('median')->nullable();
            $table->float('sd_plus_1')->nullable();
            $table->float('sd_plus_2')->nullable();
            $table->float('sd_plus_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_references');
    }
};
