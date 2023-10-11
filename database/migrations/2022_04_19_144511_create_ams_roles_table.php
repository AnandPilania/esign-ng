<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmsRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_ams_roles', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('role_name', 50)->nullable(false);
        //     $table->tinyInteger('delete_flag')->default(0);
        //     $table->integer('created_by')->nullable(true);
        //     $table->integer('updated_by')->nullable(true);
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
        Schema::dropIfExists('ec_ams_roles');
    }
}
