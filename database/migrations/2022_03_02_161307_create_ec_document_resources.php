<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_document_resources', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('document_id');
        //     $table->integer('parent_id')->default(-1);
        //     $table->string('file_name_raw', 255);
        //     $table->string('file_type_raw', 15);
        //     $table->string('file_size_raw', 100);
        //     $table->string('file_path_raw', 1024);
        //     $table->string('file_id', 100);
        //     $table->tinyInteger('status')->default(1);
        //     $table->tinyInteger('version')->default(1);
        //     $table->string('note', 255)->nullable(true);
        //     $table->integer('created_by');
        //     $table->integer('updated_by')->nullable(true);
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
        Schema::dropIfExists('ec_document_resources');
    }
}
