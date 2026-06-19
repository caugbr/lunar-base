<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
    */
    public function run()
    {
        $users = config('defaultUsers');
        foreach ($users as $usr) {
            $usr['password'] = bcrypt($usr['password']);
            \App\Models\User::create($usr);
        }
    }
}
