<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentTextInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_text_info', function (Blueprint $table) {
            $table->id();
            $table->integer('document_id');
            $table->string('matruong', 50)->nullable(true);
            $table->integer('data_type')->default(1)->comment("1: text, 2: signature, 3: checkbox, 4: radio");
            $table->string('content', 1024);
            $table->tinyInteger('font_size')->nullable(true);
            $table->string('font_style', 50)->nullable(true);
            $table->integer('page_sign')->default(1);
            $table->decimal('width_size');
            $table->decimal('height_size');
            $table->decimal('x');
            $table->decimal('y');
            $table->decimal('page_width');
            $table->decimal('page_height');
            $table->integer('created_by')->nullable(true);
            $table->integer('updated_by')->nullable(true);
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
        Schema::dropIfExists('ec_document_text_info');
    }
}
