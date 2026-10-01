<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

if (! function_exists('addRole')) {
    /**
     * Registra ou atualiza um papel (Role) no sistema.
     *
     * @param string $slug        Slug único (ex: 'coordinator')
     * @param string $name        Nome legível (ex: 'Coordenador')
     * @param string $description Descrição do papel
     * @param array  $permissions Lista inicial de permissões (opcional)
     * @return void
     */
    function addRole(string $slug, string $name, string $description = '', array $permissions = []): void
    {
        // 1. Registra a role na lista
        config([
            "rolesPermissions.roles.{$slug}" => [
                'name'        => $name,
                'description' => $description,
            ]
        ]);

        // 2. Se foram passadas permissões, atribui à role
        if (! empty($permissions)) {
            assignPermissionsToRole($slug, $permissions);
        }
    }
}

if (! function_exists('removeRole')) {
    /**
     * Remove um papel do sistema (com proteção total para o 'admin').
     *
     * @param string $slug
     * @return bool Retorna false se tentar remover o admin
     */
    function removeRole(string $slug): bool
    {
        // Proteção contra exclusão do admin ou roles estruturais do core
        if ($slug === 'admin') {
            return false;
        }

        $roles = config('rolesPermissions.roles', []);
        $permissionsByRole = config('rolesPermissions.permissionsByRole', []);

        unset($roles[$slug]);
        unset($permissionsByRole[$slug]);

        config([
            'rolesPermissions.roles'             => $roles,
            'rolesPermissions.permissionsByRole' => $permissionsByRole,
        ]);

        return true;
    }
}

if (! function_exists('addPermission')) {
    /**
     * Registra uma nova permissão em um grupo e a disponibiliza nos Gates do Laravel.
     * Automaticamente adiciona a nova permissão ao 'admin'.
     *
     * @param string      $slug        Identificador único da permissão (ex: 'view-courses')
     * @param string      $label       Descrição da permissão (ex: 'Visualizar cursos')
     * @param string      $group       Grupo onde ela reside (ex: 'cursos', 'dashboard')
     * @param string|null $groupTitle  Título legível do grupo caso ele seja novo (opcional)
     * @return void
     */
    function addPermission(string $slug, string $label, string $group = 'general', ?string $groupTitle = null): void
    {
        // 1. Registra no permissionGroups
        config([
            "rolesPermissions.permissionGroups.{$group}.{$slug}" => $label
        ]);

        // 2. O Admin sempre recebe todas as permissões registradas
        $adminPerms = config('rolesPermissions.permissionsByRole.admin', []);
        if (! in_array($slug, $adminPerms, true)) {
            $adminPerms[] = $slug;
            config(['rolesPermissions.permissionsByRole.admin' => $adminPerms]);
        }

        // 3. Registra no Gate do Laravel para habilitar can(), @can e middleware can:
        registerPermissionGate($slug);
    }
}

if (! function_exists('addPermissions')) {
    /**
     * Registra várias permissões de uma vez em um mesmo grupo.
     *
     * @param array  $permissions Array associativo ['slug' => 'Descrição']
     * @param string $group       Slug do grupo
     * @return void
     */
    function addPermissions(array $permissions, string $group = 'general'): void
    {
        foreach ($permissions as $slug => $label) {
            // Se for passado um array simples indexado por número ['view-courses', 'edit-courses']
            if (is_numeric($slug)) {
                $slug = $label;
                $label = ucfirst(str_replace(['-', '_'], ' ', $slug));
            }

            addPermission($slug, $label, $group);
        }
    }
}

if (! function_exists('assignPermissionsToRole')) {
    /**
     * Atribui uma ou mais permissões a uma Role específica.
     *
     * @param string       $roleSlug
     * @param array|string $permissions Slug único ou array de slugs
     * @return void
     */
    function assignPermissionsToRole(string $roleSlug, array|string $permissions): void
    {
        $permissions = (array) $permissions;
        $current = config("rolesPermissions.permissionsByRole.{$roleSlug}", []);

        $merged = array_unique(array_merge($current, $permissions));

        config(["rolesPermissions.permissionsByRole.{$roleSlug}" => array_values($merged)]);

        // Garante que todas essas permissões estejam registradas no Gate do Laravel
        foreach ($permissions as $perm) {
            registerPermissionGate($perm);
        }
    }
}

if (! function_exists('revokePermissionsFromRole')) {
    /**
     * Remove permissões de uma Role.
     * (O Admin não pode ter permissões revogadas por segurança).
     *
     * @param string       $roleSlug
     * @param array|string $permissions
     * @return void
     */
    function revokePermissionsFromRole(string $roleSlug, array|string $permissions): void
    {
        if ($roleSlug === 'admin') {
            return;
        }

        $permissions = (array) $permissions;
        $current = config("rolesPermissions.permissionsByRole.{$roleSlug}", []);

        $updated = array_diff($current, $permissions);

        config(["rolesPermissions.permissionsByRole.{$roleSlug}" => array_values($updated)]);
    }
}

if (! function_exists('registerPermissionGate')) {
    /**
     * Registra uma permissão no Gate do Laravel (idempotente).
     *
     * @param string $permission
     * @return void
     */
    function registerPermissionGate(string $permission): void
    {
        // Se o Gate já existe, evita redeclaração
        if (Gate::has($permission)) {
            return;
        }

        Gate::define($permission, function (User $user) use ($permission) {
            // Administrador tem superpoderes automáticos
            if ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
                return true;
            }

            // Checa pelo método hasPermission() do model se existir, senão olha o config
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($permission);
            }

            $userPermissions = config("rolesPermissions.permissionsByRole.{$user->role}", []);
            return in_array($permission, $userPermissions, true);
        });
    }
}
