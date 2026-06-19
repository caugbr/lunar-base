<?php

use App\Models\AdminLog;

if (!function_exists('log_admin')) {
    /**
     * Registra uma ação de auditoria no painel administrativo
     */
    function log_admin(string $action, string $category = 'general')
    {
        try {
            $user = auth()->user(); 
            
            \App\Models\AdminLog::create([
                'user_id'    => $user?->id,
                'user_name'  => $user?->name ?? 'Sistema/Anônimo',
                'action'     => $action,
                'category'   => $category,
                'referrer'   => request()->headers->get('referer'), 
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error("Falha ao salvar log de auditoria: " . $e->getMessage());
        }
    }
}