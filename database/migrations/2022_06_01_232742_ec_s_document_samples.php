<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EcSDocumentSamples extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_document_samples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->tinyInteger('document_type')->comment('1: Noi bo, 2: Thuong mai');
            $table->integer('document_type_id');
            $table->string('name', 255);
            $table->string('description', 1024)->nullable(true);
            $table->tinyInteger('is_verify_content')->default(0);
            $table->string('document_path_original')->nullable(true);
            $table->tinyInteger('delete_flag')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('ec_s_document_samples');
    }
}
