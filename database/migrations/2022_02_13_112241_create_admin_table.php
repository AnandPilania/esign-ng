<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ec_admins', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('email', 120);
        //     $table->string('password')->nullable(false);
        //     $table->string('full_name', 255);
        //     $table->string('address', 255)->nullable(true);
        //     $table->tinyInteger('sex')->default(-1);
        //     $table->string('note')->nullable(true);
        //     $table->date('dob')->nullable(true);
        //     $table->string('phone', 30)->nullable(true);
        //     $table->boolean('status')->default(1);
        //     $table->boolean('delete_flag')->default(0);
        //     $table->timestamp('latest_active')->nullable(true);
        //     $table->string('remember_token')->nullable(true);
        //     $table->integer('created_by')->default(-1);
        //     $table->integer('updated_by')->nullable(true);
        //     $table->integer('role_id')->default(1);
        //     $table->integer('agency_id')->nullable(true);
        //     $table->string('language', 50)->default('vi')->nullable(true);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_admins');
    }
}
