<?php

use Illuminate\Database\Seeder;

class DocumentGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ec_s_document_groups')->insert([
            [
                "company_id" => 1,
                "group_name" => "Tài liệu nội bộ",
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                "company_id" => 1,
                "group_name" => "Tài liệu thương mại",
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                "company_id" => 1,
                "group_name" => "Tài liệu đầu vào",
                'created_by' => 1,
                'updated_by' => 1
            ],
        ]);
    }
}
