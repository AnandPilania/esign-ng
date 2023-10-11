<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable(false)->default(1);
            $table->integer('department_id');
            $table->integer('position_id');
            $table->string('emp_code', 25)->nullable(true)->default(null);
            $table->string('emp_name', 255);
            $table->string('reference_code', 25)->nullable(true);
            $table->date('dob')->nullable(true);
            $table->tinyInteger('sex')->default(-1)->comment('0: Man 1: Woman, 2: Undefined');
            $table->string('ethnic', 125)->comment('Dân tộc')->nullable(true);
            $table->string('nationality', 255)->nullable(true);
            $table->string('province', 125)->nullable(true);
            $table->string('address1', 512)->comment('Quê quán')->nullable(true)->default(null);
            $table->string('address2', 512)->comment('Địa chỉ thường trú')->nullable(true)->default(null);
            $table->string('national_id', 50)->nullable(true)->nullable(true)->default(null);
            $table->date('national_date')->nullable(true);
            $table->string('national_address_provide', 255)->nullable(true)->default(null);;
            $table->string('degree', 255)->nullable(true);
            $table->string('degree_subject', 255)->nullable(true);
            $table->string('contract_type', 255)->nullable(true);
            $table->string('contract_duration', 255)->nullable(true);
            $table->string('contract_bg', 255)->nullable(true);
            $table->string('contract_ed', 255)->nullable(true);
            $table->string('address_office', 512)->nullable(true);
            $table->string('working_time', 125)->nullable(true);
            // I don't think we need to manage employee salary information in this system??
            $table->decimal('salary')->nullable(true);
            $table->decimal('salary_base')->nullable(true);
            $table->decimal('salary_extra')->nullable(true);
            $table->decimal('salary_bonus')->nullable(true);
            $table->decimal('salary_bonus_extra')->nullable(true);

            $table->string('email', 255);
            $table->string('phone', 25)->nullable(true);
            $table->string('note', 1024)->nullable(true);
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('ec_s_employees');
    }
}
