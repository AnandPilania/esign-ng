<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable(true);
            $table->string('username', 256)->nullable(false)->unique('username');
            $table->string('password', 256);
            $table->string('vendor', 256)->nullable(false);
            $table->boolean('revoked');
            $table->string('description', 1024)->nullable(true);
            $table->integer('created_by')->nullable(true);
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
        Schema::dropIfExists('ec_vendors');
    }
}
