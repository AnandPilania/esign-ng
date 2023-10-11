<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentSignature extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_signature', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_id');
            $table->integer('assign_id');
            $table->integer('page_sign')->default(1);
            $table->decimal('width_size');
            $table->decimal('height_size');
            $table->decimal('page_width');
            $table->decimal('page_height');
            $table->decimal('x');
            $table->decimal('y');
            $table->tinyInteger('is_auto_sign')->comment("0: ko, 1: tu dong ky")->nullable(true);
            // $table->string('image_path', 1024);
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
        Schema::dropIfExists('ec_document_signature');
    }
}
