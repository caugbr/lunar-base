<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CoreUpdateService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    /**
     * Processa a atualização do Core via AJAX
     */
    public function apply(CoreUpdateService $updater)
    {
        try {
            $updater->applyUpdate();
            return response()->json([
                'success' => true,
                'message' => 'Lunar Base atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Força a rechecagem no GitHub (botão manual)
     */
    public function check(CoreUpdateService $updater)
    {
        $info = $updater->checkForUpdates();

        return back()->with('info', $info['has_update']
            ? "Nova versão v{$info['latest_version']} encontrada!"
            : "O sistema já está na versão mais recente.");
    }
}
