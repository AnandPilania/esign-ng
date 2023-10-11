<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable(false);
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('phone')->nullable(true);
            $table->date('dob')->nullable(true);
            $table->string('address', 255)->nullable(true);
            $table->string('note', 512)->nullable(true);
            $table->tinyInteger('sex')->default(-1)->comment('0: Nam, 1: Nu, -1: Không xác đinh');
            $table->string('otp', 6)->default(01);
            $table->string('avatar', 255)->default(null)->nullable(true);
            $table->string('language', 50)->default('vi')->nullable(true);
            $table->dateTime('lasted_active')->default(Carbon::now());
            $table->boolean('is_personal')->default(true);
            $table->boolean('status')->default(true)->comment('0: Không được phép đăng nhập, 1: Được phép đăng nhập');
            $table->string('remember_token')->nullable(true);
            $table->tinyInteger('delete_flag')->default(0);
            $table->timestamp('expiration_time')->nullable(true);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('source')->default(1)->comment('1: created by company 0: created by admin site');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_users');
    }
}
