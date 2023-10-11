<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcSConversationTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_conversation_templates', function (Blueprint $table) {
            $table->id();
            $table->string("template_name", 50);
            $table->string("template_description", 500);
            $table->text("template")->nullable(true);
            $table->tinyInteger("type")->default(1)->comment("0: SMS 1: EMAIL");
            $table->tinyInteger("status")->default(1);
            $table->tinyInteger("is_ams")->default(1)->comment("0: ams, 1: cms");
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
        Schema::dropIfExists('ec_s_conversation_templates');
    }
}
