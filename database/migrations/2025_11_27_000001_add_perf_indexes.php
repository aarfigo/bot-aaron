<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add composite index to speed status + kitchen_cleared + date filters
        if(Schema::hasTable('tbl_order')){
            // `status` is stored as TEXT, so use a prefix index to stay below MySQL key size limits.
            if (!Schema::hasColumn('tbl_order', 'status')) {
                return;
            }
            DB::statement('ALTER TABLE tbl_order ADD INDEX tbl_order_status_cleared_date_idx (`status`(191), `kitchen_cleared`, `order_date`)');
        }
        // Index orderID on orderdetail for fast whereIn lookups
        if(Schema::hasTable('tbl_orderdetail')){
            Schema::table('tbl_orderdetail', function(Blueprint $table){
                $table->index('orderID', 'tbl_orderdetail_orderID_idx');
            });
        }
    }

    public function down(): void
    {
        if(Schema::hasTable('tbl_order')){
            Schema::table('tbl_order', function(Blueprint $table){
                $table->dropIndex('tbl_order_status_cleared_date_idx');
            });
        }
        if(Schema::hasTable('tbl_orderdetail')){
            Schema::table('tbl_orderdetail', function(Blueprint $table){
                $table->dropIndex('tbl_orderdetail_orderID_idx');
            });
        }
    }
};
