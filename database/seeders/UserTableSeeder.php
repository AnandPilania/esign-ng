<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('ec_users')->insert([
            [
                "id" => 1,
                "company_id" => 1,
                "name" => base64_encode("Its Developer"),
                "email" => base64_encode("admin@its.com"),
                "password" => Hash::make('Abcd!234'),
                'phone' => base64_encode('0972893844'),
                'address' => 'Hà Nội',
                "dob" => '1987-05-20',
                'otp' => 123,
                "sex" => 1,
                'role_id' => 1,
                'source' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
        ]);
    }
}
