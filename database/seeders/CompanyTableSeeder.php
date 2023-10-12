<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyTableSeeder extends Seeder
{
    public function run()
    {
        // Create Table Roles
        DB::table('ec_companies')->insert([
            [
                "id" => 1,
                "name" => "Its company",
                "agency_id" => 1,
                "service_id" => 1,
                "email" => "info@interits.com",
                'tax_number' => '09875621318',
                'fax_number' => '',
                'address' => 'Hà Nội',
                'phone' => '0972893844',
                'website' => 'interits.com',
                'company_code' => 'E_CON_000001',
                'representative' => 'Vũ Gia Luyện',
                'representative_position' => 'Tổng giám đốc',
                'bank_info' => 'Ngân hàng TMCP Kỹ thương Việt Nam - CN Hoàng Quốc Việt',
                'bank_number' => '19128883682669',
                'contact_name' => 'Nguyễn Văn A',
                'contact_phone' => '0912345678',
                'contact_email' => 'anv@interits.com',
                'state' => 'APPROVED',
                'status' => 1,
                'approved_by' => 1,
                'source' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ],
        ]);
    }
}
