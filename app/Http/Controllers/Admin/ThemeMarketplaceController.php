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
     * Remove completamente a pasta de um único tema do disco e do banco.
     */
    public function remove(string $folderName, AddonMarketplaceService $marketplace)
    {
        if (! $this->deleteTheme($folderName)) {
            return back()->with('error', 'Nome de tema inválido.');
        }

        // Limpa o cache do catálogo para recarregar o status
        $marketplace->clearCache();

        return back()->with('success', "Tema '{$folderName}' foi removido com sucesso!");
    }

    /**
     * Remove todos os temas inativos em lote (apenas os disponíveis no catálogo).
     */
    public function removeInactive(AddonMarketplaceService $marketplace)
    {
        // 1. Mapeia os temas remotos que realmente podem ser baixados novamente
        $downloadableThemes = collect($marketplace->getAvailableThemes())
            ->filter(fn ($item) => ! empty($item['download_url']))
            ->keyBy(fn ($item) => str_replace(' ', '', $item['folder'] ?? $item['name'] ?? ''));

        // 2. Busca todos os temas inativos no banco
        $inactiveThemes = Theme::where('is_active', false)->get();

        if ($inactiveThemes->isEmpty()) {
            return back()->with('info', 'Nenhum tema inativo encontrado para remoção.');
        }

        $deletedCount  = 0;
        $skippedCustom = 0;

        foreach ($inactiveThemes as $theme) {
            // Trava de segurança 1: nunca apaga o tema ativo
            if ($theme->is_active) {
                continue;
            }

            $folderName = $theme->folder_name;

            // Trava de segurança 2: se não tem download_url no marketplace, é um tema próprio/local. Preserva!
            if (! $downloadableThemes->has($folderName)) {
                $skippedCustom++;
                continue;
            }

            if ($this->deleteTheme($folderName)) {
                $deletedCount++;
            }
        }

        // Limpa o cache do catálogo apenas se algum tema foi deletado
        if ($deletedCount > 0) {
            $marketplace->clearCache();
        }

        if ($deletedCount === 0 && $skippedCustom > 0) {
            return back()->with('info', 'Nenhum tema foi excluído. Os temas inativos presentes são locais/próprios e foram preservados.');
        }

        $message = "{$deletedCount} tema(s) inativo(s) excluído(s) com sucesso!";
        if ($skippedCustom > 0) {
            $message .= " ({$skippedCustom} tema(s) próprio(s) preservado(s)).";
        }

        return back()->with('success', $message);
    }

    /**
     * Executa a limpeza física da pasta, link simbólico de assets e registro do banco de dados.
     */
    protected function deleteTheme(string $folderName): bool
    {
        if (empty($folderName)) {
            return false;
        }

        $themePath = base_path("themes/{$folderName}");

        // 1. Apaga a pasta física do tema
        if (File::exists($themePath)) {
            File::deleteDirectory($themePath);
        }

        // 2. Apaga o link simbólico dos assets de public/themes/{nome}
        Artisan::call('theme:link', [
            'theme'    => Str::lower($folderName),
            '--unlink' => true,
        ]);

        // 3. Apaga o registro do banco de dados
        Theme::where('folder_name', $folderName)
            ->orWhere('name', $folderName)
            ->delete();

        return true;
    }
}
