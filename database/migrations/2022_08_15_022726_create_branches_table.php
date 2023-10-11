<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_branches', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('company_id');
        //     $table->string('tax_number', 30)->nullable(true);
        //     $table->string('branch_code')->nullable(true);
        //     $table->string('name', 255);
        //     $table->string('address', 255)->nullable(true);
        //     $table->string('phone', 30)->nullable(true);
        //     $table->string('email', 255)->nullable(true);
        //     $table->tinyInteger('status')->default(1);
        //     $table->integer('created_by');
        //     $table->integer('updated_by');
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
        Schema::dropIfExists('ec_branches');
    }
}
