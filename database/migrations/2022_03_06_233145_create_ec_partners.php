<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_partners', function (Blueprint $table) {
            $table->increments("id");
            $table->integer('document_id');
            $table->tinyInteger('order_assignee')->default(1);
            $table->tinyInteger('organisation_type')->default(1)->comment('1: to chuc cua toi, 2 to chuc khac, 3 ca nhan');
            $table->string('organisation_name', 255)->nullable(true);
            $table->string('company_name', 255)->nullable(true);
            $table->string('code', 255)->nullable(true);
            $table->string('tax', 255)->nullable(true);
            $table->string('email', 255)->nullable(true);
            $table->string('address', 255)->nullable(true);
            $table->string('phone', 25)->nullable(true);
            $table->string('bank', 255)->nullable(true);
            $table->string('bank_no', 255)->nullable(true);
            $table->string('representative',  255)->nullable(true);
            $table->string('representative_position',  255)->nullable(true);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('ec_partners');
    }
}
