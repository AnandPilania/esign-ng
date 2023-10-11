<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_admin_logs', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('admin_id');
        //     $table->text('content');
        //     $table->string('function');
        //     $table->tinyInteger('delete_flag')->default(0);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_admin_logs');
    }
}
