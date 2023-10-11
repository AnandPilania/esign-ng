<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionTypeColToEcSActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_s_activities', function (Blueprint $table) {
            $table->tinyInteger('action_type')->default(0)->after('action_group')->comment('0:web;1:admin;2:ky ngoai;3:api');
            $table->string('name')->nullable(true)->after('action');
            $table->string('email')->nullable(true)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ec_s_activities', function (Blueprint $table) {
            //
        });
    }
}
