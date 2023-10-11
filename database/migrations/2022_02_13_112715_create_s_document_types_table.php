<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_document_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('document_group_id');
            $table->string('dc_type_code', 50);
            $table->string('dc_type_name', 512);
            $table->tinyInteger('is_order_auto')->default(0);
            $table->tinyInteger('dc_length')->nullable(true);
            $table->string('dc_format', 50)->nullable(true);
            $table->string('note', 512)->nullable(true);
            $table->boolean('status')->default(true);
            $table->tinyInteger('delete_flag')->default(0);
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
        Schema::dropIfExists('s_document_types');
    }
}
