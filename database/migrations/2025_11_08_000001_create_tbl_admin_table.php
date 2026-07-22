<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblAdminTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_admin', function (Blueprint $table) {
            // Use increments so the ID is auto-incrementing and primary
            $table->increments('ID');
            $table->string('username', 25);
            $table->string('password', 100);
            // no timestamps in original schema
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_admin');
    }
}
