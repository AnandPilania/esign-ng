<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DocumentAssigneeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('ec_document_assignees')->insert([
                [
                    "company_id" => 1,
                    "full_name" => "Nam Nguyen",
                    "email" => "namnvhut@gmail.com",
                    "phone" => "0972893844",
                    "national_id" => 1,
                    "address" => 'Hanoi',
                    "ext_info" => '{}',
                    "document_id" => 1,
                    "partner_id" => 1,
                    "message" => "Thông báo yêu cầu duyệt tài liệu",
                    'noti_type' => 0,
                    'order' => 1,
                    'status' => 1,
                    'delete_flag' => 0,
                    'is_internal' => 0,
                    'state' => 0,
                    'reason' => "Thông báo yêu cầu duyệt tài liệu",
                    'submit_time' => \Carbon\Carbon::now(),
                    'assign_type' => 0,
                    'sign_method' => "",
                    'is_required' => 0,
                    'password' => Hash::make('Abcd!234'),
                    'url_code' => "abc_123456689",
                    'otp' => "865492",
                    'created_by' => 1,
                    'updated_by' => 1
                ]
            ]
        );
    }
}
