<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentApprovementProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_approvement_process', function (Blueprint $table) {
            $table->increments("id");
            $table->integer('document_id');
            $table->integer('assign_id');
            $table->tinyInteger("state")->default(0)->comment("0: Waiting 1: Approved , -1: Rejected");
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
        Schema::dropIfExists('ec_document_approvement_process');
    }
}
