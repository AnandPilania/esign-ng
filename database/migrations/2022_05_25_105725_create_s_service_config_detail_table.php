<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSServiceConfigDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_service_config_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("service_config_id");
            $table->integer("from");
            $table->integer("to");
            $table->integer("fee");
            $table->integer("delete_flag")->default(0);
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
        Schema::dropIfExists('s_service_config_detail');
    }
}
