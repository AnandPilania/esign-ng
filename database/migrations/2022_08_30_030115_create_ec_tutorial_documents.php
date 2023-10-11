<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcTutorialDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_tutorial_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('status')->default(0);
            $table->string('name', 255);
            $table->string('description', 1024)->nullable(true);
            $table->tinyInteger('version')->default(1);
            $table->tinyInteger('delete_flag')->default(0);
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
        Schema::dropIfExists('ec_tutorial_documents');
    }
}
