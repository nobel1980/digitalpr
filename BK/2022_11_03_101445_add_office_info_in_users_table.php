<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfficeInfoInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sccode')->nullable()->after('password');
            $table->string('prj_code')->nullable()->after('sccode');
            $table->string('desig_code')->nullable()->after('prj_code');
            $table->string('desig_name')->nullable()->after('desig_code');
            $table->string('dept_code')->nullable()->after('desig_name');
            $table->string('dept_name')->nullable()->after('dept_code');
            $table->string('off_code')->nullable()->after('dept_name');
            $table->string('off_type')->nullable()->after('off_code');
            $table->string('zone_code')->nullable()->after('off_type');
            $table->string('div_code')->nullable()->after('zone_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sccode');
            $table->dropColumn('prj_code');
            $table->dropColumn('desig_code');
            $table->dropColumn('desig_name');
            $table->dropColumn('desig_name');
            $table->dropColumn('off_code');
            $table->dropColumn('off_type');
            $table->dropColumn('zone_code');
            $table->dropColumn('div_code');
        });
    }
}
