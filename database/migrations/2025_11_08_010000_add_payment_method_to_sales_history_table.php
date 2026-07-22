<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sales_history')) {
            Schema::table('sales_history', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_history', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('total');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sales_history')) {
            Schema::table('sales_history', function (Blueprint $table) {
                if (Schema::hasColumn('sales_history', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
            });
        }
    }
};
