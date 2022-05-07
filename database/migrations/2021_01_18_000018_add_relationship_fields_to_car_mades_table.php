<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToCarMadesTable extends Migration
{
    public function up()
    {
        Schema::table('car_mades', function (Blueprint $table) {
            $table->unsignedBigInteger('categoryid_id')->nullable();
            $table->foreign('categoryid_id', 'categoryid_fk_2976059')->references('id')->on('product_categories')->onDelete('cascade');
        });
    }
}
