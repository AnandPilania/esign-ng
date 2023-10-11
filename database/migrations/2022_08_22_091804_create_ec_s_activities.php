<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('data_table', 500)->nullable(true);
            $table->string('action', 255)->nullable(true);
            $table->string('note', 500)->nullable(true);
            $table->text('raw_log')->nullable(true);
            $table->integer('created_by')->nullable(true);
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
        Schema::dropIfExists('ec_s_activities');
    }
}
