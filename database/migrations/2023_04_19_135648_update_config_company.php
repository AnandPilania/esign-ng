<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateConfigCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `ec_s_company_config` CHANGE `theme_color` `theme_header_color` VARCHAR(255);');

        Schema::table('ec_s_company_config', function(Blueprint $table)
        {
            $table->string('theme_footer_color')->after('theme_header_color')->default('#206bc4');
            $table->integer('file_size_upload')->after('theme_footer_color')->default(5);
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
