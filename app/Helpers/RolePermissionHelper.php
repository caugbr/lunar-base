<?php

if (!function_exists('isRole')) {
    /**
     * Verifica se o usuário autenticado possui o role informado.
     */
    function isRole(string $role): bool
    {
        $user = auth()->user();

        return $user?->hasRole($role) ?? false;
    }
}

if (!function_exists('userCan')) {
    /**
     * Verifica se o usuário autenticado possui a permissão informada.
     */
    function userCan(string $permission): bool
    {
        $user = auth()->user();

        return $user?->hasPermission($permission) ?? false;
    }
}
