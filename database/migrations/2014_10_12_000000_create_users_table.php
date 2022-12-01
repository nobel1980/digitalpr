<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emp_code')->unique();
            $table->string('mobile_number')->unique();
            $table->string('mac_address')->unique();
            $table->string('ip_address')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('sccode');
            $table->string('prj_code');
            $table->string('desig_code');
            $table->string('desig_name');
            $table->string('dept_code');
            $table->string('dept_name');
            $table->string('off_code');
            $table->string('off_type');
            $table->string('zone_code');
            $table->string('div_code');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
