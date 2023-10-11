<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentSampleInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_document_sample_info', function (Blueprint $table) {
            $table->id();
            $table->integer('document_sample_id');
            $table->integer('data_type')->comment("1: text, 2: signature, 3: checkbox, 4: radio");
            $table->string('content', 512)->nullable(true);
            $table->string('description', 1024);
            $table->tinyInteger('is_required')->comment("0: khong bat buoc, 1: bat buoc")->nullable(true);
            $table->tinyInteger('is_editable')->comment("0: khong cho sua, 1: cho phep sua")->nullable(true);
            $table->string('form_name', 50)->nullable(true);
            $table->string('field_code', 50)->nullable(true);
            $table->string('form_description', 1024)->nullable(true);
            $table->tinyInteger('font_size')->nullable(true);
            $table->string('font_style', 50)->nullable(true);
            $table->integer('page_sign')->default(1);
            $table->decimal('width_size');
            $table->decimal('height_size');
            $table->decimal('x');
            $table->decimal('y');
            $table->decimal('page_width');
            $table->decimal('page_height');
            $table->tinyInteger('order_assignee')->default(1)->nullable(true);
            $table->tinyInteger('is_my_organisation')->default(1)->comment('0: khong phai, 1: to chuc cua toi')->nullable(true);
            $table->tinyInteger('is_auto_sign')->comment("0: ko, 1: tu dong ky")->nullable(true);
            $table->string('sign_method', 50)->nullable(true)->comment("Cach thuc ky: 0: ky usbtoken/remote signing, 1: ky otp, 2: ky ekyc");
            $table->text("image_signature")->nullable(true);
            $table->string('full_name', 255)->nullable(true);
            $table->string('email')->nullable(true);
            $table->string('phone', 50)->nullable(true);
            $table->string('national_id', 50)->nullable(true);
            $table->tinyInteger('noti_type')->nullable(true)->comment("0: ko gui, 1: email, 2: sms, 3: all");
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
        Schema::dropIfExists('ec_s_document_sample_info');
    }
}
