<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcCompanyConsigneesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_company_consignees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('name', 125);
            $table->string('email', 255);
            $table->string('phone', 30)->nullable(true);
            $table->tinyInteger('role')->comment('1: Phe duyet, 2: ky so tai lieu');
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
        Schema::dropIfExists('ec_company_consignees');
    }
}
