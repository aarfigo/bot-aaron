<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sales_history')) {
            Schema::table('sales_history', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_history', 'reference')) {
                    $table->string('reference')->nullable()->after('payment_method');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sales_history')) {
            Schema::table('sales_history', function (Blueprint $table) {
                if (Schema::hasColumn('sales_history', 'reference')) {
                    $table->dropColumn('reference');
                }
            });
        }
    }
};
