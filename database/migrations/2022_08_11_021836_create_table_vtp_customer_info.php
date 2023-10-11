<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableVtpCustomerInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtp_customer_info', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable(true);
            $table->string('front_image_url')->nullable(true);
            $table->string('back_image_url')->nullable(true);
            $table->tinyInteger('verify_status')->nullable(true)->comment("0: not verify", "1: verified");
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
        Schema::dropIfExists('table_vtp_customer_info');
    }
}
