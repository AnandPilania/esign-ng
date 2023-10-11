<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSServiceConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_service_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('service_code', 50);
            $table->string('service_name', 255);
            $table->string('description', 255)->nullable(true);
            $table->tinyInteger('service_type')->comment("1: theo so luong hop dong, 2: theo thoi gian su dung");
            $table->tinyInteger("status")->default(1);
            $table->tinyInteger('delete_flag')->default(0);
            $table->integer('updated_by')->nullable(true);
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
        Schema::dropIfExists('s_service_config');
    }
}
