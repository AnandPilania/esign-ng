<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSDocumentGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_document_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable(false);
            $table->string('group_name', 100);
            $table->smallInteger('group_order')->default(1);
            $table->tinyInteger('is_resolve')->default(0);
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
        Schema::dropIfExists('ec_s_document_groups');
    }
}
