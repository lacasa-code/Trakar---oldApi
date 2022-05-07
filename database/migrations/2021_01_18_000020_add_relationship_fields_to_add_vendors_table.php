<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToAddVendorsTable extends Migration
{
    public function up()
    {
        Schema::table('add_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('userid_id')->nullable();
            $table->foreign('userid_id', 'userid_fk_2999059')->references('id')->on('users');
        });
    }
}
