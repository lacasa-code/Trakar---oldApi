<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarMadesTable extends Migration
{
    public function up()
    {
        Schema::create('car_mades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('car_made')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
