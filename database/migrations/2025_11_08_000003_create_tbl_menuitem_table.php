<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblMenuitemTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_menuitem', function (Blueprint $table) {
            $table->increments('itemID');
            $table->integer('menuID')->unsigned();
            $table->text('menuItemName');
            $table->decimal('price', 15, 2);
            // add index for menuID
            $table->index('menuID');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_menuitem');
    }
}
