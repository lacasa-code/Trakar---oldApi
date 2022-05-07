<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarYearsTable extends Migration
{
    public function up()
    {
        Schema::create('car_years', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('year');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
