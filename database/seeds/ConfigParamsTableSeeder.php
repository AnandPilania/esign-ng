<?php

use Illuminate\Database\Seeder;

class ConfigParamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ec_s_config_params')->insert([
            [
                "company_id" => 1,
                "send_email_remind_day" => 2,
                'document_expire_day' => 60,
            ],
        ]);
    }
}
