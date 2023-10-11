<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id'); // we accept redundant data for this column
            $table->integer('document_id');
            $table->tinyInteger('notify_type')->default(0)->comment('0: reminder 1: rejected 2: approval');
            $table->integer('send_id')->comment('Sender');
            $table->tinyInteger('send_type')->comment("0: sms, 1: email");
            $table->text('content');
            $table->tinyInteger("template_id");
            $table->tinyInteger('status')->default(0)->comment('0: chua gui 1: da gui, 2: gui loi');
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
        Schema::dropIfExists('ec_document_conversations');
    }
}
