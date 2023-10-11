<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDocumentSignatureKycTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_document_signature_kyc', function (Blueprint $table) {
            $table->tinyInteger('sign_type')->after("assign_id")->nullable(true)->comment("Cach thuc ky: 0: ky usbtoken/remote signing, 1: ky otp, 2: ky ekyc");
            $table->binary("x509_certificate")->after("sign_type")->nullable(true);
            $table->dateTime('signed_at')->after("x509_certificate")->nullable(true);
            $table->string('password', 255)->after('signed_at')->nullable(true);
            $table->binary("pri_key")->after("password")->nullable(true);
            $table->binary("pub_key")->after("pri_key")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
