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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->string('no_kk');
            $table->string('member_name');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->date('birthdate');
            $table->string('birthplace')->nullable();
            $table->enum('category', [
                'balita',
                'anak-remaja',
                'dewasa',
                'lansia',
                'ibu hamil'
            ])->nullable();
            $table->string('father')->nullable();
            $table->string('mother')->nullable();
            $table->string('nik_parent')->nullable();
            $table->string('parent_phone')->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
