<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Table Roles
        DB::table('ec_agencies')->insert([
            [
                "id" => 1,
                "agency_name" => base64_encode("Its Corp"),
                "agency_phone" => base64_encode("0972893844"),
                "agency_email" => base64_encode("example@its.com"),
                "state" => 'APPROVED',
                'agency_address' => 'Hà Nội',
                'created_by' => 1,
                'updated_by' => 1,
                'version' => 1,
                'status' => 1
            ],
        ]);
    }
}
