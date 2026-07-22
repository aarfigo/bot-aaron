<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    public function run()
    {
    DB::table('tbl_orderdetail')->insertOrIgnore([
            ['orderID'=>1,'orderDetailID'=>1,'itemID'=>14,'quantity'=>1,'comment'=>''],
            ['orderID'=>2,'orderDetailID'=>2,'itemID'=>13,'quantity'=>1,'comment'=>''],
            ['orderID'=>2,'orderDetailID'=>3,'itemID'=>14,'quantity'=>1,'comment'=>''],
            ['orderID'=>2,'orderDetailID'=>4,'itemID'=>15,'quantity'=>1,'comment'=>''],
            ['orderID'=>2,'orderDetailID'=>5,'itemID'=>16,'quantity'=>1,'comment'=>''],
            ['orderID'=>3,'orderDetailID'=>6,'itemID'=>25,'quantity'=>1,'comment'=>''],
            ['orderID'=>3,'orderDetailID'=>7,'itemID'=>38,'quantity'=>1,'comment'=>''],
            ['orderID'=>3,'orderDetailID'=>8,'itemID'=>32,'quantity'=>1,'comment'=>''],
            ['orderID'=>4,'orderDetailID'=>9,'itemID'=>17,'quantity'=>1,'comment'=>''],
            ['orderID'=>4,'orderDetailID'=>10,'itemID'=>30,'quantity'=>1,'comment'=>''],
            ['orderID'=>5,'orderDetailID'=>11,'itemID'=>17,'quantity'=>2,'comment'=>''],
            ['orderID'=>6,'orderDetailID'=>12,'itemID'=>23,'quantity'=>1,'comment'=>''],
            ['orderID'=>6,'orderDetailID'=>13,'itemID'=>26,'quantity'=>1,'comment'=>''],
            ['orderID'=>6,'orderDetailID'=>14,'itemID'=>36,'quantity'=>1,'comment'=>''],
            ['orderID'=>7,'orderDetailID'=>15,'itemID'=>19,'quantity'=>2,'comment'=>'El cliente quiere un platillo con jamon y el otro con salchicha'],
            ['orderID'=>7,'orderDetailID'=>16,'itemID'=>31,'quantity'=>1,'comment'=>''],
            ['orderID'=>7,'orderDetailID'=>17,'itemID'=>32,'quantity'=>1,'comment'=>''],
            ['orderID'=>7,'orderDetailID'=>18,'itemID'=>37,'quantity'=>1,'comment'=>'Con mucha crema'],
            ['orderID'=>8,'orderDetailID'=>19,'itemID'=>17,'quantity'=>1,'comment'=>'Bien Cocidos'],
        ]);
    }
}
