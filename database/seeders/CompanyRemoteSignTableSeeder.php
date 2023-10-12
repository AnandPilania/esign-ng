<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyRemoteSignTableSeeder extends Seeder
{
    public function run()
    {
        // Create Table Roles
        DB::table('ec_company_remote_sign')->insert([
            [
                "company_id" => "1",
                "provider" => "BKAV",
                "service_signing" => "abc",
                "login" => "NAD",
                "password" => "",
                'status' => 1,
                'delete_flag' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
        ]);
    }
}
