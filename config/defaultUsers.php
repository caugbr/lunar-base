<?php

// config/defaultUsers.php
//
// Este arquivo gera o array de usuários padrão para o AdminUsersSeeder.
// Prioridade de leitura:
//   1. Se existir storage/app/.install/default_users_data.json, usa os dados dele.
//   2. Caso contrário, gera os dados automaticamente a partir de rolesPermissions.php.
//
// O JSON é gerado pelo script install.sh durante a instalação interativa.

// Resolve o path do JSON de forma robusta, funcionando mesmo antes
// do framework estar totalmente bootstrapped.
$jsonPath = function_exists('storage_path')
    ? storage_path('app/.install/default_users_data.json')
    : __DIR__ . '/../storage/app/.install/default_users_data.json';

if (file_exists($jsonPath)) {
    $jsonContent = file_get_contents($jsonPath);
    $users = json_decode($jsonContent, true);

    if (is_array($users) && count($users) > 0 && json_last_error() === JSON_ERROR_NONE) {
        return $users;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Fallback: gera usuários automaticamente a partir das roles
// ─────────────────────────────────────────────────────────────────────────────

$config = include __DIR__ . '/rolesPermissions.php';

$createRoleUsers = true;

// Tenta obter o host do APP_URL; se não disponível, usa 'lunar.base' como padrão
$appUrl = null;
if (function_exists('env')) {
    $appUrl = env('APP_URL');
}
$mailHost = 'lunar.base';
if (!empty($appUrl)) {
    $parsed = preg_replace("#^https?://([^/:]+).*$#", "$1", $appUrl);
    if (!empty($parsed)) {
        $mailHost = $parsed;
    }
}

$users = [];

if ($createRoleUsers) {
    foreach ($config['roles'] as $slug => $roleData) {
        $users[] = [
            'name' => $roleData['name'],
            'email' => $slug . '@' . $mailHost,
            'password' => 'Pass#1029',
            'role' => $slug
        ];
    }
}

return $users;
