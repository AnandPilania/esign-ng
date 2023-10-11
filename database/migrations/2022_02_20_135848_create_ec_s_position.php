<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSPosition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->default(1)->nullable(false);
            $table->string('name', 255);
            $table->string('position_code', 25);
            $table->string('note', 512)->nullable(true);
            $table->boolean('status')->default(true);
            $table->tinyInteger('delete_flag')->default(0);
            $table->integer('created_by')->nullable(true);
            $table->integer('updated_by')->nullable(true);;
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
        Schema::dropIfExists('ec_s_positions');
    }
}
