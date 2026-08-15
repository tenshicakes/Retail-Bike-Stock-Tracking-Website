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
                'Username' => 'Juls',
                'Password' => Hash::make('juls123'), 
                'Role' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Username' => 'Jeremiah Macc',
                'Password' => Hash::make('staff123'),
                'Role' => 'Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Username' => 'Alvin',
                'Password' => Hash::make('owner123'),
                'Role' => 'Owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Username' => 'Oggy',
                'Password' => Hash::make('mech123'),
                'Role' => 'Mechanic',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);
    }
}
