<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    public function run()
    {
    DB::table('tbl_menuitem')->insertOrIgnore([
            ['itemID'=>17,'menuID'=>8,'menuItemName'=>'Huevos en Caserola','price'=>5000.00],
            ['itemID'=>19,'menuID'=>8,'menuItemName'=>'Huevos Revueltos','price'=>5000.00],
            ['itemID'=>20,'menuID'=>8,'menuItemName'=>'Carne en Bistec','price'=>6000.00],
            ['itemID'=>21,'menuID'=>8,'menuItemName'=>'Calentado','price'=>6000.00],
            ['itemID'=>22,'menuID'=>9,'menuItemName'=>'Filete de Pollo','price'=>6000.00],
            ['itemID'=>23,'menuID'=>9,'menuItemName'=>'Filete de Carne','price'=>6000.00],
            ['itemID'=>24,'menuID'=>9,'menuItemName'=>'Chuleta de Pollo','price'=>6000.00],
            ['itemID'=>25,'menuID'=>9,'menuItemName'=>'Chuleta de Res','price'=>6000.00],
            ['itemID'=>26,'menuID'=>9,'menuItemName'=>'Chuleta de Cerdo','price'=>6000.00],
            ['itemID'=>27,'menuID'=>9,'menuItemName'=>'Res Asada','price'=>6000.00],
            ['itemID'=>28,'menuID'=>9,'menuItemName'=>'Pollo Asado','price'=>6000.00],
            ['itemID'=>29,'menuID'=>9,'menuItemName'=>'Pollo Frito','price'=>6000.00],
            ['itemID'=>30,'menuID'=>10,'menuItemName'=>'Tinto','price'=>1000.00],
            ['itemID'=>31,'menuID'=>10,'menuItemName'=>'Café con Leche','price'=>1200.00],
            ['itemID'=>32,'menuID'=>10,'menuItemName'=>'Gaseosa 350 ML','price'=>2000.00],
            ['itemID'=>33,'menuID'=>10,'menuItemName'=>'Gaseosa 200 ML','price'=>1500.00],
            ['itemID'=>34,'menuID'=>10,'menuItemName'=>'Jugo Natural en Agua','price'=>4000.00],
            ['itemID'=>35,'menuID'=>10,'menuItemName'=>'Jugo Natural en Leche','price'=>5000.00],
            ['itemID'=>36,'menuID'=>10,'menuItemName'=>'Jugo de Naranja','price'=>3500.00],
            ['itemID'=>37,'menuID'=>11,'menuItemName'=>'Cremas','price'=>3000.00],
            ['itemID'=>38,'menuID'=>11,'menuItemName'=>'Sancocho','price'=>3000.00],
            ['itemID'=>39,'menuID'=>11,'menuItemName'=>'Caldo de Costilla','price'=>3000.00],
        ]);
    }
}
