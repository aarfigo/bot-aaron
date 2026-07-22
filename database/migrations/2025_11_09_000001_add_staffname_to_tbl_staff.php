<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStaffnameToTblStaff extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tbl_staff', 'staffName')) {
            Schema::table('tbl_staff', function (Blueprint $table) {
                $table->string('staffName', 100)->nullable()->after('staffID');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tbl_staff', 'staffName')) {
            Schema::table('tbl_staff', function (Blueprint $table) {
                $table->dropColumn('staffName');
            });
        }
    }
}
