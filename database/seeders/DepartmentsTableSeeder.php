<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ec_s_departments')->insert([
            [
                "company_id" => 1,
                "name" => "Hành chính",
                "department_code" => "0001",
                "delete_flag" => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            ],
            [
                "company_id" => 1,
                "name" => "Nhân sự",
                "department_code" => "0002",
                "delete_flag" => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
