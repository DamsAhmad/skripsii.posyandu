<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //     /**
    //      * Run the migrations.
    //      */
    //    public function up()
    // {
    //     Schema::table('members', function (Blueprint $table) {
    //         $table->dropColumn('category');
    //         $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
    //     });
    // }

    // public function down()
    // {
    //     Schema::table('members', function (Blueprint $table) {
    //         $table->dropForeign(['category_id']);
    //         $table->dropColumn('category_id');
    //         $table->enum('category', ['balita', 'anak-remaja', 'dewasa', 'lansia', 'ibu hamil'])->nullable();
    //     });
    // }

};
