<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentHsmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_hsm', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agreementuuid', 255)->nullable(true);
            $table->integer('assignee_id')->nullable(true);
            $table->integer('document_id')->nullable(true);
            $table->string('passcode',255)->nullable(true);
            $table->boolean('status')->nullable(true);
            $table->timestamps();
        });
        Schema::create('ec_document_hsm_log', function (Blueprint $table) {
            $table->increments('id');
            $table->json('content')->nullable(true);
            $table->string('path',255)->nullable(true);
            $table->integer('response_code')->nullable(true);
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
        //
    }
}
