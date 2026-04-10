<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => 'Cau',
            'email' => 'caugbr@gmail.com',
            'password' => bcrypt('Abre1234#1'),
            'role_id' => 1
        ]);
        \App\Models\User::create([
            'name' => 'Editor',
            'email' => 'editor@gmail.com',
            'password' => bcrypt('Abre1234#1'),
            'role_id' => 2
        ]);
        \App\Models\User::create([
            'name' => 'Viewer',
            'email' => 'viewer@gmail.com',
            'password' => bcrypt('Abre1234#1'),
            'role_id' => 3
        ]);
    }
}
