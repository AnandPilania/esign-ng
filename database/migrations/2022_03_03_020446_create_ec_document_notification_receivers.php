<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentNotificationReceivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_notification_receivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('notification_id');
            $table->integer('receiver_id');
            $table->string('full_name');  //redundant data -> accepting because this data never update
            $table->string('email'); //redundant data -> accepting because this data never update
            $table->string('phone'); //redundant data -> accepting because this data never update
            $table->tinyInteger('is_attendant')->comment('0: Giao ket 1: Subscribe');
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
        Schema::dropIfExists('ec_document_notification_receivers');
    }
}
