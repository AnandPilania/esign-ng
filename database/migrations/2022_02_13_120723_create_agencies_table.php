<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_agencies', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('agency_name', 255)->nullable(true);
        //     $table->string('agency_phone', 30)->nullable(true);
        //     $table->string('agency_fax', 30)->nullable(true);
        //     $table->string('agency_email', 255);
        //     $table->string('agency_address', 255);
        //     $table->tinyInteger('status')->default(1);
        //     $table->enum('state', ['OPEN', 'IN-PROCESS', 'APPROVED', 'REJECTED', 'CLOSED'])->default('OPEN');
        //     $table->tinyInteger('delete_flag')->default(0);
        //     $table->integer('created_by');
        //     $table->integer('updated_by')->nullable(true);
        //     $table->integer('version')->default(1);
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
        Schema::dropIfExists('ec_agencies');
    }
}
