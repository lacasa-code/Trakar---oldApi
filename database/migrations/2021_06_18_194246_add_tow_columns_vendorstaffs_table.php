<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTowColumnsVendorstaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendorstaffs', function (Blueprint $table) {
           $table->unsignedBigInteger('vendor_id')->nullable();
           $table->string('vendor_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendorstaffs', function (Blueprint $table) {
            //
        });
    }
}
