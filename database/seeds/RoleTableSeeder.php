<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ec_s_roles')->insert([
            [
                "id" => 1,
                "company_id" => 1,
                "role_name" => "Administrator",
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            ],
            [
                "id" => 2,
                "company_id" => 1,
                "role_name" => "Xem tài liệu",
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            ],
            [
                "id" => 3,
                "company_id" => 1,
                "role_name" => "Ký tài liệu",
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
