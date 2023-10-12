<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AmsRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Table Roles
        DB::table('ec_ams_roles')->insert([
            [
                "id" => 1,
                "role_name" => "ADMIN",
            ],
            [
                "id" => 2,
                "role_name" => "AGENCY"
            ]
        ]);
    }
}
