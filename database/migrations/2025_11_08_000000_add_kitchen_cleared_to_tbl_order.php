<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Only attempt to alter the table if it exists and the column is missing.
        if (Schema::hasTable('tbl_order') && ! Schema::hasColumn('tbl_order', 'kitchen_cleared')) {
            Schema::table('tbl_order', function (Blueprint $table) {
                $table->boolean('kitchen_cleared')->default(false)->after('status');
            });
        }
    }

    public function down()
    {
        // Only attempt to drop the column if the table exists and the column exists.
        if (Schema::hasTable('tbl_order') && Schema::hasColumn('tbl_order', 'kitchen_cleared')) {
            Schema::table('tbl_order', function (Blueprint $table) {
                $table->dropColumn('kitchen_cleared');
            });
        }
    }
};
