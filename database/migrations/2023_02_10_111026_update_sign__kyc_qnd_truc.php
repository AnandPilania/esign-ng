<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSignKycQndTruc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_kyc_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('api_path')->nullable(true);
            $table->integer('code')->nullable(false);
            $table->json('content')->nullable(true);
            $table->integer('document_id')->nullable(true);
            $table->string('vendor')->nullable(true);
            $table->integer('company_id')->nullable(true);
            $table->tinyInteger('type')->nullable(true);
            $table->integer('object_id')->nullable(true);
            $table->bigInteger('end_time')->nullable(true);
            $table->bigInteger('start_time')->nullable(true);
            $table->timestamps();
        });
        Schema::table('ec_companies', function (Blueprint $table) {
            $table->integer('source_method')->after('company_code')->default(0);
        });
        Schema::table('ec_document_resources_ex', function (Blueprint $table) {
            $table->string('hash', 255)->nullable(true);
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
