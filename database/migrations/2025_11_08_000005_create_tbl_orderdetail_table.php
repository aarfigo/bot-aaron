<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblOrderdetailTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_orderdetail', function (Blueprint $table) {
            $table->integer('orderID')->unsigned();
            $table->increments('orderDetailID');
            $table->integer('itemID')->unsigned();
            $table->integer('quantity');
            $table->string('comment', 100);
            $table->index('itemID');
            $table->index('orderID');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_orderdetail');
    }
}
