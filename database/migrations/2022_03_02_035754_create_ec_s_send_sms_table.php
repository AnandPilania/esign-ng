<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSSendSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_send_sms', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('service_provider', 255);
            $table->string('service_url', 255);
            $table->string('brandname', 255)->nullable(true);
            $table->string('sms_account', 255);
            $table->string('sms_password');
            $table->tinyInteger('status')->default(true);
            $table->integer('created_by')->nullable(true);
            $table->integer('updated_by')->nullable(true);
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
        Schema::dropIfExists('ec_s_send_sms');
    }
}
