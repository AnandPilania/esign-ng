<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_companies', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('agency_id')->nullable(true);
        //     $table->integer('service_id')->nullable(true);
        //     $table->string('name', 255);
        //     $table->string('company_code')->nullable(true);
        //     $table->string('tax_number', 30);
        //     $table->tinyInteger('sign_type')->default(1)->nullable(true);
        //     $table->string('fax_number', 50)->nullable(true);
        //     $table->string('address', 255);
        //     $table->string('phone', 30)->nullable(true);
        //     $table->string('email', 255)->nullable(true);
        //     $table->string('website', 250)->nullable(true);
        //     $table->string('representative', 255)->nullable(true);
        //     $table->string('representative_position', 125)->nullable(true);
        //     $table->string('bank_info', 500)->nullable(true);
        //     $table->string('bank_number', 250)->nullable(true);
        //     $table->string('contact_name', 250)->nullable(true);
        //     $table->string('contact_phone', 50)->nullable(true);
        //     $table->string('contact_email', 255)->nullable(true);
        //     $table->enum('state', ['OPEN', 'IN-PROCESS', 'APPROVED', 'REJECTED', 'CLOSED'])->default('OPEN');
        //     $table->tinyInteger('status')->default(1);
        //     $table->integer('version')->default(1);
        //     $table->integer('approved_by')->nullable(true);
        //     $table->integer('created_by');
        //     $table->integer('updated_by');
        //     $table->tinyInteger('source')->default(1);
        //     $table->tinyInteger('delete_flag')->default(0);
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
        Schema::dropIfExists('ec_companies');
    }
}
