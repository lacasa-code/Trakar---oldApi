<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('add_vendors', function (Blueprint $table) {
            $table->string('commercial_no')->nullable();
            $table->string('commercial_doc')->nullable();
            $table->string('tax_card_no')->nullable();
            $table->string('tax_card_doc')->nullable();
            $table->string('bank_account')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('add_vendors', function (Blueprint $table) {
        });
    }
}
