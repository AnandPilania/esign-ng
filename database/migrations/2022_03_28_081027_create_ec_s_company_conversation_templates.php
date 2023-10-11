<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSCompanyConversationTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_company_conversation_templates', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger("template_id");
            $table->integer("company_id");
            $table->text("template")->nullable(true);
            $table->tinyInteger("type")->default(1)->comment("0: SMS 1: EMAIL");
            $table->tinyInteger("status")->default(1);
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
        Schema::dropIfExists('ec_s_company_conversation_templates');
    }
}
