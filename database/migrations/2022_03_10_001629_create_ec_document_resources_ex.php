<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentResourcesEx extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_resources_ex', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('document_id');
            $table->integer("parent_id")->default(-1);
            $table->string('document_path_original');
            $table->string('document_path_sign')->nullable(true);
            $table->integer('created_by')->nullable(true)->comment('This field will get from assignee table');
            $table->tinyInteger('status')->default(true);
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
        Schema::dropIfExists('ec_document_resources_ex');
    }
}
