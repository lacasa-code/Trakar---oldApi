<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->string('last_name')->nullable();
            $table->string('area')->nullable();
            $table->string('district')->nullable();
            $table->string('home_no')->nullable();
            $table->string('floor_no')->nullable();
            $table->string('apartment_no')->nullable();
            $table->string('telephone_no')->nullable();
            $table->string('nearest_milestone')->nullable();
            $table->string('notices')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            //
        });
    }
}
