<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tbl_order', 'created_by')) {
            Schema::table('tbl_order', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('attended_by');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tbl_order', 'created_by')) {
            Schema::table('tbl_order', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
