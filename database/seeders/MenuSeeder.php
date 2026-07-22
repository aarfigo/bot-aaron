<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run()
    {
    DB::table('tbl_menu')->insertOrIgnore([
            ['menuID' => 8, 'menuName' => 'Desayunos'],
            ['menuID' => 9, 'menuName' => 'Carnes'],
            ['menuID' => 10, 'menuName' => 'Bebidas'],
            ['menuID' => 11, 'menuName' => 'Sopas'],
        ]);
    }
}
