<?php
namespace Database\Seeders;

class DocumentTypeSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('ec_m_document_types')->insert([
           [
                "id" => 1,
               'name' => 'Nội bộ',
               'delete_flag' => 0
           ],
            [
                "id" => 2,
                'name' => 'Thương mại',
                'delete_flag' => 0
            ]
        ]);
    }
}
