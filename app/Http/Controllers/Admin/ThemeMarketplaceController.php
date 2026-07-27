<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AddonMarketplaceService;
use App\Services\AddonInstallerService;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeMarketplaceController extends Controller
{
    /**
     * Exibe o Marketplace de Temas.
     */
    public function index(AddonMarketplaceService $marketplace)
    {
        $themes = $marketplace->getAvailableThemes();

        return view('admin.themes.marketplace', compact('themes'));
    }

    /**
     * Instala múltiplos temas selecionados na view em lote.
     */
    public function installBatch(Request $request, AddonInstallerService $installer)
    {
        $selectedThemes = $request->input('selected_themes', []);

        if (empty($selectedThemes)) {
            return back()->with('error', 'Nenhum tema foi selecionado para instalação.');
        }

        $installedCount = 0;
        $failedThemes   = [];

        foreach ($selectedThemes as $addon) {
            $data = is_string($addon) ? json_decode($addon, true) : $addon;

            $name        = $data['name'] ?? null;
            $downloadUrl = $data['download_url'] ?? null;

            if (! $name || ! $downloadUrl) {
                continue;
            }

            // Instala como tipo 'theme'
            $success = $installer->installFromUrl($name, $downloadUrl, 'theme');

            if ($success) {
                $installedCount++;
            } else {
                $failedThemes[] = $name;
            }
        }

        // Sincroniza temas no banco de dados
        if (method_exists($this, 'syncThemes')) {
            $this->syncThemes();
        }

        if ($installedCount === 0) {
            return back()->with('error', 'Falha ao instalar os temas selecionados.');
        }

        $message = "{$installedCount} tema(s) instalado(s) com sucesso!";
        if (count($failedThemes) > 0) {
            $message .= " Falha em: " . implode(', ', $failedThemes);
        }

        return back()->with('success', $message);
    }

    /**
     * Limpa o cache do catálogo de temas.
     */
    public function refresh(AddonMarketplaceService $marketplace)
    {
        $marketplace->clearCache();

        return back()->with('success', 'Catálogo de temas atualizado com sucesso!');
    }

    /**
     * Remove completamente a pasta do tema do disco e do banco.
     */
    public function remove(string $folder, AddonMarketplaceService $marketplace)
    {
        // 1. Sanitiza o nome da pasta em StudlyCase por segurança
        $folderName = Str::studly($folder);

        if (empty($folderName)) {
            return back()->with('error', 'Nome de tema inválido.');
        }

        $themePath = base_path("themes/{$folderName}");

        // 2. Apaga a pasta física do tema
        if (File::exists($themePath)) {
            File::deleteDirectory($themePath);
        }

        // 3. Apaga o link simbólico dos assets de public/themes/{nome}
        Artisan::call('theme:link', [
            'theme' => Str::lower($folderName),
            '--unlink' => true,
        ]);

        // 4. Apaga o registro do banco de dados
        Theme::where('folder_name', $folderName)
            ->orWhere('name', $folderName)
            ->delete();

        // 5. Limpa o cache do catálogo para recarregar o status
        $marketplace->clearCache();

        return back()->with('success', "Tema '{$folderName}' foi removido com sucesso!");
    }
}
