<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchIdUserDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('ec_documents', function (Blueprint $table) {
            $table->integer('branch_id')->after('customer_id')->nullable(true);
        });
        Schema::table('ec_users', function (Blueprint $table) {
            $table->integer('branch_id')->after('source')->nullable(true);
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
