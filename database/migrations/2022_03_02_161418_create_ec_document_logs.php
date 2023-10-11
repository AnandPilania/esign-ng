<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcDocumentLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_document_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_id');
            $table->json('prev_value');
            $table->json('next_value');
            $table->string('content', 1024);
            $table->tinyInteger('action')->default(1)->comment('1: tao moi, 2: cap nhat, 3: hoan thanh, 4: gui mail, 5: tu choi phe duyet, 6: phe duyet, 7: tu choi ky, 8: ky');
            $table->string('action_by', 100)->nullable(true);
            $table->string('action_by_email', 100)->nullable(true);
            $table->tinyInteger('is_show')->default(1)->comment('0: Do not show content 1: Show content');
            $table->integer('created_by')->nullable(true)->comment('Belong authorized people');
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
        Schema::dropIfExists('ec_document_logs');
    }
}
