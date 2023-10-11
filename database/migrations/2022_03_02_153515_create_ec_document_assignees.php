<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentAssignees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_document_assignees', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('company_id')->nullable(true);
        //     $table->string('full_name', 255);
        //     $table->string('email');
        //     $table->string('phone', 50)->nullable(true);
        //     $table->string('national_id', 50)->nullable(true);
        //     $table->string('address', 255)->nullable(true);
        //     $table->json('ext_info')->nullable(true);
        //     $table->integer('document_id');
        //     $table->integer('partner_id');
        //     $table->string('message', 255)->nullable(true);
        //     $table->tinyInteger('noti_type')->comment("0: ko gui, 1: email, 2: sms, 3: all");
        //     $table->tinyInteger('order')->default(1);
        //     $table->tinyInteger('status')->default(1);
        //     $table->tinyInteger('delete_flag')->default(0);
        //     $table->tinyInteger('is_internal')->default(1);
        //     $table->tinyInteger("state")->default(0)->comment("0: Chua nhan thong bao, 1: da nhan thong bao , 2: da giao ket, 3: reject");
        //     $table->string("reason", 1024)->nullable(true)->comment("Reason must have in the case assignee reject document");
        //     $table->dateTime("submit_time")->nullable(true)->comment("The time submit reject");
        //     $table->tinyInteger('assign_type')->default(0)->comment('0: nguoi tao, 1: phe duyet, 2: ky, 3: xem');
        //     $table->string('sign_method', 50)->nullable(true)->comment("Cach thuc ky: 0: ky usbtoken/remote signing, 1: ky otp, 2: ky ekyc, 3: ký giấy");
        //     $table->tinyInteger('is_required')->default(1);
        //     $table->string('password', 255)->nullable(true);
        //     $table->string('url_code', 255)->nullable(true);
        //     $table->string('otp', 10)->nullable(true);
        //     $table->integer('created_by')->nullable(true);
        //     $table->integer('updated_by')->nullable(true);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_document_assigns');
    }
}
