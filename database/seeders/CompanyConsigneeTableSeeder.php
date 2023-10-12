<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyConsigneeTableSeeder extends Seeder
{
    public function run()
    {
        // Create Table Roles
        DB::table('ec_company_consignees')->insert([
            [
                "company_id" => "1",
                "name" => "Người ký 1",
                'email' => 'nk1@gmail.com',
                'role' => '1',
                'delete_flag' => 0,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                "company_id" => "1",
                "name" => "Người ký 2",
                'email' => 'nk2@gmail.com',
                'role' => '1',
                'delete_flag' => 0,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                "company_id" => "1",
                "name" => "Người ký 3",
                'email' => 'nk3@gmail.com',
                'role' => '1',
                'delete_flag' => 0,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
        ]);
    }
}
