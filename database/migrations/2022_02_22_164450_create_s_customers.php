<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('name', 125);
            $table->string('code', 50);
            $table->string('tax_number', 125)->nullable(true);
            $table->string('address', 512);
            $table->string('phone', 30)->nullable(true);
            $table->string('email', 255);
            $table->string('bank_info', 255)->nullable(true);
            $table->string('bank_account', 255)->nullable(true);
            $table->string('bank_number', 255)->nullable(true);
            $table->string('representative', 255)->nullable(true);
            $table->string('representative_position', 255)->nullable(true);
            $table->string('contact_name', 255)->nullable(true);
            $table->string('contact_phone', 255)->nullable(true);
            $table->string('note', 1024)->nullable(true);
            $table->tinyInteger('customer_type')->default(1)->comment('0: personal 1: organisation');
            $table->tinyInteger('status')->default(true);
            $table->tinyInteger('delete_flag')->default(0);
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
        Schema::dropIfExists('ec_s_customers');
    }
}
