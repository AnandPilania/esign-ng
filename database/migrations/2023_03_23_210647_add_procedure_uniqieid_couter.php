<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcedureUniqieidCouter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_sys_uniqieid_counter', function (Blueprint $table) {
            $table->string('context_id')->nullable(false);
            $table->string('context_name')->nullable(true);
            $table->decimal('max_count',18,0)->nullable(true);
            $table->decimal('count',18,0)->nullable(true);
            $table->datetime('create_time')->nullable(true);
            $table->datetime('update_time')->nullable(true);
            $table->primary('context_id');
        });

        $procedure = "DROP PROCEDURE IF EXISTS COUNT_UNIQUE;
        CREATE PROCEDURE COUNT_UNIQUE(IN seq_name varchar(100), OUT id INT)
        BEGIN
          DECLARE cur_val bigint;
          SET id = 1;
          START TRANSACTION;
          UPDATE sc_sys_uniqieid_counter SET COUNT = COUNT + 1 WHERE context_id = seq_name;
          SELECT COUNT INTO cur_val FROM sc_sys_uniqieid_counter WHERE context_id = seq_name;
          COMMIT;
          SELECT cur_val + 0 as COUNTER;
          SET id = cur_val + 0;
        END;
        ";

        \DB::unprepared($procedure);
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
