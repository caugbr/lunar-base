<?php

/**
 * =============================================================================
 * LUNAR BASE - USUÁRIOS PADRÃO (SEEDER)
 * =============================================================================
 *
 * Gera dinamicamente a lista de usuários iniciais/demonstração com base
 * nos papéis (roles) registrados em rolesPermissions.php.
 * Utilizado primariamente pelo AdminUsersSeeder durante o 'php artisan db:seed'.
 * =============================================================================
 */

$config = include __DIR__ . '/rolesPermissions.php';

// Obtém o domínio do site a partir do APP_URL (ex: lunar.base)
$appUrl = function_exists('env') ? env('APP_URL') : null;
$mailHost = 'lunar.base';

if (!empty($appUrl)) {
    $parsed = preg_replace("#^https?://([^/:]+).*$#", "$1", $appUrl);
    if (!empty($parsed)) {
        $mailHost = $parsed;
    }
}

$users = [];

// Cria um usuário correspondente para cada papel do sistema com senha padrão
if (!empty($config['roles']) && is_array($config['roles'])) {
    foreach ($config['roles'] as $slug => $roleData) {
        $users[] = [
            'name'     => $roleData['name'] ?? ucfirst($slug),
            'email'    => $slug . '@' . $mailHost,
            'password' => 'Pass#1029',
            'role'     => $slug,
        ];
    }
}

return $users;
