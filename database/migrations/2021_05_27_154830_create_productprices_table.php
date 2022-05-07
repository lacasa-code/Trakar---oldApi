<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductpricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productprices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('producttype_id')->nullable();
            $table->decimal('price', 30, 2)->nullable();
            $table->unsignedBigInteger('num_of_orders')->nullable();
            $table->text('serial_coding_seq')->nullable();
            $table->integer('status')->default(1);
            $table->string('lang')->default('ar');
            $table->string('currency')->default('sr');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productprices');
    }
}
