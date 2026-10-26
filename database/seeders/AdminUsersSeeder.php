<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = config('defaultUsers', []);

        foreach ($users as $usr) {
            User::firstOrCreate(
                ['email' => $usr['email']], // Condição de busca única
                [
                    'name'     => $usr['name'],
                    'password' => Hash::make($usr['password']),
                    'role'     => $usr['role'],
                ]
            );
        }
    }
}
