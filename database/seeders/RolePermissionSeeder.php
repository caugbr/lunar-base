<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Administrador', 'slug' => 'admin', 'description' => 'Acesso total ao sistema'],
            ['id' => 2, 'name' => 'Editor', 'slug' => 'editor', 'description' => 'Gerencia páginas e conteúdo'],
            ['id' => 3, 'name' => 'Visualizador', 'slug' => 'viewer', 'description' => 'Apenas visualiza o dashboard'],
        ]);

        // 2. Definir permissões por role
        $permissions = [
            1 => [ // Admin: todas as permissões
                'view-dashboard', 'manage-users', 'manage-pages', 'manage-roles', 'edit-profile',
            ],
            2 => [ // Editor: gerencia páginas e perfil
                'view-dashboard', 'manage-pages', 'edit-profile',
            ],
            3 => [ // Visualizador: só vê o dashboard
                'view-dashboard',
            ],
        ];

        foreach ($permissions as $roleId => $perms) {
            foreach ($perms as $perm) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission' => $perm,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
