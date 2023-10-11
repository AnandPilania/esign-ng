<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcAgencyLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_agency_logs', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('company_id')->default(1)->nullable(true);
        //     $table->string('prev_state', 50);
        //     $table->string('next_state', 50);
        //     $table->string('note', 512);
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
        Schema::dropIfExists('ec_agency_logs');
    }
}
