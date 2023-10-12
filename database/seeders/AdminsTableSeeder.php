<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Table Roles
        DB::table('ec_admins')->insert([
            [
                "id" => 1,
                "agency_id" => -1,
                "full_name" => base64_encode("Its Developers"),
                "email" => base64_encode("admin@its.com"),
                "password" => Hash::make('Abcd!234'),
                'address' => 'Hà Nội',
                'latest_active' => Carbon::now(),
                'role_id' => 1,
                'created_by'=>1,
                'status' => 1
            ],
        ]);
        if (env('RUN_DB_SEED', false)) {
            for ($i = 4; $i < 10; $i++) {
                $email  = "dev2022-$i@its.com";
                DB::table('ec_admins')->insert([
                    [
                        "id" => 1,
                        "agency_id" => -1,
                        "full_name" => base64_encode("ITS".$i),
                        'address' => 'Hà Nội',
                        'latest_active' => Carbon::now(),
                        "email" => base64_encode($email),
                        "password" => Hash::make('Abcd!234'),
                        "role_id" => 2,
                        'created_by'=>1,
                        'status' => rand(0, 1)
                    ],
                ]);
            }
        }
    }
}
