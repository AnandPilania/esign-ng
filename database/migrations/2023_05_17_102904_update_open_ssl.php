<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOpenSsl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_documents', function(Blueprint $table)
        {
            $table->tinyInteger('is_encrypt')->default(0);
        });
        Schema::table('ec_document_resources_ex', function(Blueprint $table)
        {
            $table->binary('save_password')->nullable(true);
        });
        Schema::table('ec_s_document_samples', function(Blueprint $table)
        {
            $table->tinyInteger('is_encrypt')->default(0);
            $table->binary('save_password')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
