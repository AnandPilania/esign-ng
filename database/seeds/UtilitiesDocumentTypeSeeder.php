<?php

use \Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\DB;

class UtilitiesDocumentTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('s_document_types')->insert([
           [
               'company_id' => 1,
               'document_group_id' => 1,
               'dc_type_code' => 'HDLD',
               'dc_type_name' => 'Hợp đồng lao động',
               'is_order_auto' => '1',
               'dc_length' => 3,
               'dc_format' => '{number}/{YY}-{code}',
               'delete_flag' => 0
           ],
            [
                'company_id' => 1,
                'document_group_id' => 2,
                'dc_type_code' => 'KT',
               'dc_type_name' => 'Hợp đồng kinh tế',
               'is_order_auto' => '1',
               'dc_length' => 3,
               'dc_format' => '{number}/{YYYY}-{code}',
               'delete_flag' => 0
            ]
        ]);
    }
}
