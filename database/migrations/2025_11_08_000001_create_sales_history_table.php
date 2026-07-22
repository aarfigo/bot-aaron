<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orderID')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->date('order_date')->nullable();
            $table->unsignedBigInteger('cleaned_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_history');
    }
};
