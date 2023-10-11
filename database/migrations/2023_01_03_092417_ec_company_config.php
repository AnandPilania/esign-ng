<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EcCompanyConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_s_company_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable(false);
            $table->mediumText('logo_dashboard');
            $table->mediumText('logo_login');
            $table->string('logo_sign')->default('blue');
            $table->mediumText('logo_background');
            $table->mediumText('fa_icon');
            $table->string('theme_color')->default('#206bc4');
            $table->string('step_color')->default('yellow');
            $table->string('name_app')->default('Fcontract');
            $table->string('text_color')->default('white');
            $table->mediumText('loading');
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
        //
    }
}
