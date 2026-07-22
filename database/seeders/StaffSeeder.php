<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    public function run()
    {
    DB::table('tbl_staff')->insertOrIgnore([
            ['staffID'=>1,'username'=>'Juan','password'=>'1234abcd..','status'=>'Online','role'=>'chef'],
            ['staffID'=>4,'username'=>'Pedro','password'=>'1234abcd..','status'=>'Online','role'=>'Mesero'],
            ['staffID'=>5,'username'=>'Emily','password'=>'1234abcd..','status'=>'Online','role'=>'chef'],
            ['staffID'=>6,'username'=>'Robert','password'=>'1234abcd..','status'=>'Online','role'=>'chef'],
            ['staffID'=>7,'username'=>'Sofia','password'=>'abc123','status'=>'Offline','role'=>'Mesero'],
            ['staffID'=>9,'username'=>'Marin','password'=>'1234abcd..','status'=>'Online','role'=>'chef'],
        ]);
    }
}
