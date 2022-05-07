<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddVendorsTable extends Migration
{
    public function up()
    {
        Schema::create('add_vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vendor_name');
            $table->string('email');
            $table->string('type');
            $table->string('serial')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
