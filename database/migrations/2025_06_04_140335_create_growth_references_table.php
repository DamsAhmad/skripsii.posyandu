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
            $table->string('indicator'); // bbu / imtu
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->integer('age_months');
            $table->float('median');
            $table->float('sd_minus'); // -1 SD
            $table->float('sd_plus');  // +1 SD (baru)
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
