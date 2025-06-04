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
        Schema::create('bfa_boys', function (Blueprint $table) {
            $table->id();
            $table->float('Month', 5, 2);
            $table->float('L', 8, 6);
            $table->float('M', 8, 6);
            $table->float('S', 8, 6);
            $table->float('SD4neg', 4, 2);
            $table->float('SD3neg', 4, 2);
            $table->float('SD2neg', 4, 2);
            $table->float('SD1neg', 4, 2);
            $table->float('SD0', 4, 2);
            $table->float('SD1', 4, 2);
            $table->float('SD2', 4, 2);
            $table->float('SD3', 4, 2);
            $table->float('SD4', 4, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bfa_boys');
    }
};
