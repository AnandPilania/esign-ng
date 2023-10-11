<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsReplayEmailConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_s_send_email', function (Blueprint $table) {
            $table->tinyInteger('is_relay')->after('is_use_ssl')->default(0);
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `ec_s_send_email` MODIFY `email_password` VARCHAR(2048) NULL;');
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
