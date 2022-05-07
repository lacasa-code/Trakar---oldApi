<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddVendorRejectedreasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('add_vendor_rejectedreason', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('add_vendor_id')->nullable();
            $table->unsignedBigInteger('rejectedreason_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('add_vendor_rejectedreason');
    }
}
