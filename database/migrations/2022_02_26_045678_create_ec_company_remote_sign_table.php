<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcCompanyRemoteSignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_company_remote_sign', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unique();
            $table->string('provider');
            $table->string('service_signing');
            $table->string('login');
            $table->string('password');
            $table->tinyInteger('status')->default(true);
            $table->tinyInteger('delete_flag')->default(true);
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
        Schema::dropIfExists('ec_company_remote_sign');
    }
}
