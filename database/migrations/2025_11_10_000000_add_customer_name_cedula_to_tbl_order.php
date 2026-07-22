<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('tbl_order')) {
            Schema::table('tbl_order', function (Blueprint $table) {
                if (! Schema::hasColumn('tbl_order', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('customer_table');
                }
                if (! Schema::hasColumn('tbl_order', 'customer_cedula')) {
                    $table->string('customer_cedula')->nullable()->after('customer_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('tbl_order')) {
            Schema::table('tbl_order', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_order', 'customer_cedula')) {
                    $table->dropColumn('customer_cedula');
                }
                if (Schema::hasColumn('tbl_order', 'customer_name')) {
                    $table->dropColumn('customer_name');
                }
            });
        }
    }
};
