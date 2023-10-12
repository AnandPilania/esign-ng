<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ServiceConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Table Roles
        DB::table('s_service_config')->insert([
            [
                "service_code" => "S001",
                "service_name" => "S001",
                "description" => "",
                "service_type" => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                "service_code" => "S002",
                "service_name" => "S002",
                "description" => "",
                "service_type" => 2,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ]);
    }
}
