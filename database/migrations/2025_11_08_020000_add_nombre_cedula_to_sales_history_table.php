<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds nullable `nombre` and `cedula` columns to `sales_history` so
     * the staff can record a customer name and ID at archive time.
     */
    public function up()
    {
        if (Schema::hasTable('sales_history')) {
            Schema::table('sales_history', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_history', 'nombre')) {
                    $table->string('nombre')->nullable()->after('order_date');
                }
                if (! Schema::hasColumn('sales_history', 'cedula')) {
                    $table->string('cedula')->nullable()->after('nombre');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('sales_history')) {
            Schema::table('sales_history', function (Blueprint $table) {
                if (Schema::hasColumn('sales_history', 'cedula')) {
                    $table->dropColumn('cedula');
                }
                if (Schema::hasColumn('sales_history', 'nombre')) {
                    $table->dropColumn('nombre');
                }
            });
        }
    }
};
