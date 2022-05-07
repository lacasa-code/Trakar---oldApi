<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarNavbarColumnAllcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allcategories', function (Blueprint $table) {
            $table->unsignedBigInteger('car_navbar')->nullable();
            $table->unsignedBigInteger('commercial_navbar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allcategories', function (Blueprint $table) {
            //
        });
    }
}
