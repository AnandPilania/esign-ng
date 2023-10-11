<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->tinyInteger('document_type')->comment('1: Noi bo, 2: Thuong mai');
            $table->integer('document_type_id');
            $table->tinyInteger('status')->default(0)->comment('0: Đang tạo 1: Hoàn thành');
            $table->tinyInteger('is_order_approval')->default(0);
            $table->tinyInteger('is_verify_content')->default(0);
            $table->tinyInteger('is_request_confirmed')->default(1)->comment('Need request confirm');
            $table->tinyInteger('is_request_org_confirmed')->default(1)->comment('Need an organisation confirm');
            $table->tinyInteger('document_draft_state')->default(1)->comment("1->3: buoc 1 -> 3");
            $table->tinyInteger('document_state')->default(0)->comment('1: nhap, 2: cho duyet, 3: cho ky so, 4: tu choi, 5: qua han, 6: huy bo, 7: chua xac thuc, 8: hoan thanh');
            $table->dateTime('sent_date')->nullable(true);
            $table->dateTime('expired_date')->nullable(true);
            $table->dateTime('finished_date')->nullable(true);
            $table->string('name', 255);
            $table->string('code', 50);
            $table->string('transaction_id', 512)->nullable(true);
            $table->string('description', 1024)->nullable(true);
            $table->tinyInteger('version')->default(1);
            $table->tinyInteger('delete_flag')->default(0);
            $table->integer('current_assignee_id')->nullable(true);
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
        Schema::dropIfExists('ec_documents');
    }
}
