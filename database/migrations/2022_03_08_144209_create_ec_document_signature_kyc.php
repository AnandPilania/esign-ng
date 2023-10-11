<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentSignatureKyc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_signature_kyc', function (Blueprint $table) {
            $table->id();
            $table->integer('assign_id');
            $table->text("image_signature");
            $table->string('front_image_url')->nullable(true);
            $table->string('back_image_url')->nullable(true);
            $table->string('face_image_url')->nullable(true);
            $table->integer('created_by')->nullable(true);
            $table->integer('updated_by')->nullable(true);
            $table->string('national_id', 50)->nullable(true);
            $table->string('name', 50)->nullable(true);
            $table->string('birthday', 50)->nullable(true);
            $table->string('sex', 50)->nullable(true);
            $table->string('hometown', 500)->nullable(true);
            $table->string('address', 500)->nullable(true);
            $table->string('issueDate', 50)->nullable(true);
            $table->string('issueBy', 500)->nullable(true);
            $table->string('sim', 50)->nullable(true);
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
        Schema::dropIfExists('ec_document_signature_kyc');
    }
}
