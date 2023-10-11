<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentSubscribe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_subscribe', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('document_id');
            $table->tinyInteger('status')->default(1);
            $table->string('note', 512);
            $table->integer('subscribe_id')->comment('Subscriber only supports for internal employee');
            $table->tinyInteger('delete_flag')->default(0);
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
        Schema::dropIfExists('ec_document_subscribe');
    }
}
