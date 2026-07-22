<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
    DB::table('tbl_order')->insertOrIgnore([
            ['orderID'=>1,'status'=>'finish','total'=>1000.00,'order_date'=>'2020-01-17','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>2,'status'=>'finish','total'=>10000.00,'order_date'=>'2020-01-17','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>3,'status'=>'ready','total'=>11000.00,'order_date'=>'2020-01-18','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>4,'status'=>'cancelled','total'=>6000.00,'order_date'=>'2020-01-18','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>5,'status'=>'preparing','total'=>10000.00,'order_date'=>'2020-01-25','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>6,'status'=>'waiting','total'=>15500.00,'order_date'=>'2020-01-25','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>7,'status'=>'ready','total'=>16200.00,'order_date'=>'2025-02-28','customer_table'=>null,'attended_by'=>null],
            ['orderID'=>8,'status'=>'preparing','total'=>5000.00,'order_date'=>'2025-02-28','customer_table'=>null,'attended_by'=>null],
        ]);
    }
}
