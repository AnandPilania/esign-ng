<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSDocumentTemplateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_document_template_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_id');
            $table->string('file_name', 255);
            $table->decimal('file_size')->nullable(true);
            $table->string('file_path', 1024);
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
        Schema::dropIfExists('ec_s_document_template_files');
    }
}
