<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblOrderTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_order', function (Blueprint $table) {
            $table->increments('orderID');
            $table->text('status');
            // include kitchen_cleared by default so fresh installs/tests have the column
            $table->boolean('kitchen_cleared')->default(false);
            $table->decimal('total', 15, 2);
            $table->date('order_date');
            // fields added dynamically by original app; include nullable columns
            $table->string('customer_table', 128)->nullable();
            $table->integer('attended_by')->unsigned()->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_order');
    }
}
