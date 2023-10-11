<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSDocumentTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_document_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->default(1)->nullable(false);
            $table->integer('document_type_id');
            $table->string('document_code', 50);
            $table->string('document_name', 512);
            $table->tinyInteger('is_publish')->default(0);
            $table->tinyInteger('is_publish_urgent')->default(0);
            $table->integer('created_by')->nullable(true);
            $table->integer('updated_by')->nullable(true);;
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
        Schema::dropIfExists('ec_s_document_templates');
    }
}
