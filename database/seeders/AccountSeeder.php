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

        DB::table('accounts')->insert([
            [
                'Username' => 'owner@gmail.com',
                'Password' => Hash::make('owner123'), 
                'Role' => 'Owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);

        DB::table('accounts')->insert([
            [
                'Username' => 'staff@gmail.com',
                'Password' => Hash::make('staff123'), 
                'Role' => 'Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);


        DB::table('accounts')->insert([
            [
                'Username' => 'mechanic@gmail.com',
                'Password' => Hash::make('mechanic123'), 
                'Role' => 'Mechanic',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);


        
    }
}
