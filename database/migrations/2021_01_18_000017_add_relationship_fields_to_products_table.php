<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('car_made_id')->nullable();
            $table->foreign('car_made_id', 'car_made_fk_2980105')->references('id')->on('car_mades');
            $table->unsignedBigInteger('car_model_id')->nullable();
            $table->foreign('car_model_id', 'car_model_fk_2980106')->references('id')->on('car_models');
            $table->unsignedBigInteger('year_id')->nullable();
            $table->foreign('year_id', 'year_fk_2980107')->references('id')->on('car_years');
            $table->unsignedBigInteger('part_category_id')->nullable();
            $table->foreign('part_category_id', 'part_category_fk_2980108')->references('id')->on('part_categories');
        });
    }
}
