<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceColumnAndQuantityIntoEcServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // \Illuminate\Support\Facades\DB::statement('  UPDATE ec_companies SET total_doc = "0" WHERE id > 0; ALTER TABLE ec_companies MODIFY COLUMN total_doc INTEGER;');

        Schema::table('s_service_config', function(Blueprint $table)
        {
            $table->integer('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('expires_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
