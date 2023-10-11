<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEcDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_documents', function (Blueprint $table) {
            $table->integer('expired_type')->after("code")->default(0)->comment('0:vo thoi han;1:co thoi han den ngay;2:co thoi han ke tu ngay');
            $table->dateTime('doc_expired_date')->after("expired_type")->nullable(true);
            $table->integer('expired_month')->after("expired_type")->nullable(true)->default(0);
            $table->integer("parent_id")->default(-1)->after('document_type_id');
            $table->tinyInteger("addendum_type")->nullable(true)->default(-1)->comment('0:bo sung; 1:gia han; 2: huy bo')->after('parent_id');
        });
        //
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
