<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEcSActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_s_activities', function (Blueprint $table) {
            $table->tinyInteger('company_id')->nullable(true)->after('id');
            $table->tinyInteger('action_group')->nullable(true)->after('company_id')->comment('1: Ls tài liệu; 2: Ls thiết lập; 3: Ls tiện ích; 4: Ls cá nhân');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
