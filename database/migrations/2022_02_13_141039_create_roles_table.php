<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name');
            $table->tinyInteger('role_number')->default(0);
            $table->integer('company_id')->nullable(false);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->boolean('status')->default(true);
            $table->tinyInteger('delete_flag')->default(0);
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
        Schema::dropIfExists('ec_roles');
    }
}
