<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shippings', function (Blueprint $table) {
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
      //  Schema::dropIfExists('shippings');
    }
}
