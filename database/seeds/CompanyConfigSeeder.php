<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

       DB::table('ec_s_company_config')->insert([
            [
                "id" => 1,
                "company_id" => 1,
                "theme_header_color" => '#206bc4',
                "theme_footer_color" => '#206bc4',
                "step_color" => 'yellow',
                "name_app" => 'Fcontract',
                "text_color" => 'white',
                "logo_sign" => "blue",
                "logo_login" => "images/fcontract-logo.png",
                "logo_background" => "images/bg-white-login.png",
                "fa_icon" => "fcontract-favicon.png",
                "loading" => "images/loading.jpg",
                "logo_dashboard" => "images/fcontract-dashboard.png",
            ],
        ]);
    }
}
