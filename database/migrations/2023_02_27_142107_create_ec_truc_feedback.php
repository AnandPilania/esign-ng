<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcTrucFeedback extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_truc_feedback', function (Blueprint $table) {
            $table->id();
            $table->json('feedback')->nullable(true);
            $table->integer('source')->default(0);
            $table->dateTime('create_at')->nullable(true);;
            $table->integer('max_doing')->nullable(true);
            $table->tinyInteger('state')->nullable(true);
            $table->string('transaction_id',255)->nullable(true);
            $table->string('verification_code',255)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_truc_feedback');
    }
}
