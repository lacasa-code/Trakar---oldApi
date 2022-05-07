<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorstaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendorstaffs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('role_name')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->integer('approved')->default(0);
            $table->integer('status')->default(1);
            $table->string('lang')->default('ar')->nullable();
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
        Schema::dropIfExists('vendorstaffs');
    }
}
