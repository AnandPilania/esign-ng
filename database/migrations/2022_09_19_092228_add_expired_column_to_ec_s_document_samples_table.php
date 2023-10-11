<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiredColumnToEcSDocumentSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_s_document_samples', function (Blueprint $table) {
            $table->tinyInteger('expired_type')->after("description")->default(0)->comment('0:vo thoi han;1:co thoi han den ngay;2:co thoi han ke tu ngay');
            $table->integer('expired_month')->after("expired_type")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ec_s_document_samples', function (Blueprint $table) {
            //
        });
    }
}
