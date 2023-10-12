<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VendorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Table Roles
        DB::table('ec_vendors')->insert([
            [
                "id" => 1,
                "vendor" => base64_encode("Its Developers"),
                "username" => "vtpost",
                "password" => Hash::make('Abcd!234'),
                'description' => '',
                'created_by'=> 1,
                'revoked' => 0
            ],
        ]);
    }
}
