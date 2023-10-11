<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcRolePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_role_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id');
            $table->integer('permission_id');
            $table->tinyInteger('is_view')->default(0)->nullable(true);
            $table->tinyInteger('is_write')->default(0)->nullable(true);
            $table->tinyInteger('is_approval')->default(0)->nullable(true);
            $table->tinyInteger('is_decision')->default(0)->nullable(true);
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
        Schema::dropIfExists('ec_s_role_permission');
    }
}
