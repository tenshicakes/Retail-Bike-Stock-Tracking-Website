<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;   
use Illuminate\Support\Facades\Hash; 

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('accounts')->insert([
            [
                'Username' => 'admin@gmail.com',
                'Password' => Hash::make('admin123'), 
                'Role' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);
    }
}
