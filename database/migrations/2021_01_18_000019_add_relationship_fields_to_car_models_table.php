<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToCarModelsTable extends Migration
{
    public function up()
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->unsignedBigInteger('carmade_id')->nullable();
            $table->foreign('carmade_id', 'carmade_fk_2976138')->references('id')->on('car_mades');
        });
    }
}
