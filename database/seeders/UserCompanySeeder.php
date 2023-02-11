<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=0; $i < 21 ; $i++) { 
            DB::table('user_company')->insert([
                'user_id' => rand(1,20),
                'company_id' => rand(1,10)
            ]);
        }

        // php artisan db:seed --class=UserCompanySeeder
    }
}
