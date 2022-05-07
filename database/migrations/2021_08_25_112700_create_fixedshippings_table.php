<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedshippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixedshippings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_alt_phone')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable(); 
            $table->string('state')->nullable(); 
            $table->string('country_code')->nullable(); 
            $table->string('postal_code')->nullable(); 
            $table->string('latitude')->nullable(); 
            $table->string('longitude')->nullable(); 
            $table->integer('status')->default(1); 
            $table->string('last_name')->nullable();
            $table->string('area')->nullable();
            $table->string('district')->nullable();
            $table->string('home_no')->nullable();
            $table->string('floor_no')->nullable();
            $table->string('apartment_no')->nullable();
            $table->string('telephone_no')->nullable();
            $table->string('nearest_milestone')->nullable();
            $table->string('notices')->nullable();
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
        Schema::dropIfExists('fixedshippings');
    }
}
