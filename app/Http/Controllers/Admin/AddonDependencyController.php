<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AddonInstallerService;
use App\Services\AddonMarketplaceService;
use Illuminate\Http\Request;

class AddonDependencyController extends Controller
{
    /**
     * Baixa e instala a dependência faltante diretamente
     */
    public function install(Request $request, AddonInstallerService $installer, AddonMarketplaceService $marketplace)
    {
        $name = $request->input('name');
        $downloadUrl = $request->input('download_url');
        $type = $request->input('type', 'plugin');

        if (!$name || !$downloadUrl) {
            return back()->with('error', 'Dados insuficientes para instalar o complemento.');
        }

        $success = $installer->installFromUrl($name, $downloadUrl, $type);

        if ($success) {
            $marketplace->clearCache();
            return back()->with('success', "Complemento '{$name}' baixado com sucesso! Agora você já pode ativá-lo.");
        }

        return back()->with('error', "Falha ao baixar o complemento '{$name}'.");
    }
}
