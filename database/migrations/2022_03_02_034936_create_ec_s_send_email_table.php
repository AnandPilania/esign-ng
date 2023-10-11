<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSSendEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_send_email', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('email_host', 255);
            $table->string('email_protocol', 255);
            $table->string('email_address', 255);
            $table->string('email_password');
            $table->string('email_name', 255)->nullable(true); //ten nguoi gui
            $table->integer('port');
            $table->tinyInteger('is_use_ssl')->default(false);
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
        Schema::dropIfExists('ec_s_send_email');
    }
}
