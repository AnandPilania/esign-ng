<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('permission', 100)->unique(true);
            $table->string('note', 512);
            $table->string('parent_permission', 100)->nullable(true);
            $table->tinyInteger('is_view')->nullable(true);
            $table->tinyInteger('is_write')->nullable(true);
            $table->tinyInteger('is_approval')->nullable(true);
            $table->tinyInteger('is_decision')->nullable(true);
            $table->tinyInteger('status')->default(true);
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
        Schema::dropIfExists('ec_s_permissions');
    }
}
