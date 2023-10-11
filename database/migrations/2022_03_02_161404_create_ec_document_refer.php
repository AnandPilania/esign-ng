<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentRefer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_document_refer', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('document_id');
        //     $table->string('file_att_name');
        //     $table->string('file_att_path');
        //     $table->string('note');
        //     $table->string('refer_link');
        //     $table->string('is_attach');
        //     $table->integer('created_by');
        //     $table->integer('updated_by');
        //     $table->tinyInteger('status')->default(1);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_document_refer');
    }
}
