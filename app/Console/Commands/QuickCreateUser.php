<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class QuickCreateUser extends Command
{
    protected $signature = 'user:quick-create {name} {email} {password} {role=subscriber}';
    protected $description = 'Cria ou atualiza um usuário rapidamente pelo terminal';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');
        $role = $this->argument('role');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $role
            ]
        );

        $this->info("Sucesso! Usuário {$name} ({$email}) criado/atualizado com o perfil '{$role}'.");
    }
}
