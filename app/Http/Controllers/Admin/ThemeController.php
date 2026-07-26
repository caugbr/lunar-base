<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ThemeController extends Controller
{
    /**
     * Display the theme gallery and synchronize with directories.
     */
    public function index()
    {
        $this->syncThemes();

        $themes = Theme::orderBy('name')->get();

        return view('admin.themes.index', compact('themes'));
    }

    /**
     * Activate a selected theme and deactivate all others.
     */
    public function activate(Theme $theme)
    {
        Theme::where('id', '!=', $theme->id)->update(['is_active' => false]);

        $theme->update(['is_active' => true]);

        $this->syncAllThemeAssetLinks($theme);

        return back()->with('success', "Theme '{$theme->name}' has been activated!");
    }

    /**
     * Toggle the active state of a theme.
     */
    public function toggle(Theme $theme)
    {
        // Caso 1: Se o tema já estiver ativo, desativamos ele (ficamos sem tema ativo)
        if ($theme->is_active) {
            $theme->update(['is_active' => false]);

            // Passamos `null` para garantir que TODOS os links de temas sejam removidos do disco
            $this->syncAllThemeAssetLinks(null);

            return back()->with('success', "Tema '{$theme->name}' foi desativado.");
        }

        // Caso 2: Se o tema estava inativo, desativamos TODOS no banco e ativamos este
        Theme::query()->update(['is_active' => false]);
        $theme->update(['is_active' => true]);

        // Sincroniza o disco para manter apenas o link do tema atual
        $this->syncAllThemeAssetLinks($theme);

        return back()->with('success', "Tema '{$theme->name}' foi ativado!");
    }

    /**
     * Garante que APENAS o $activeTheme tenha link simbólico no disco.
     * Se $activeTheme for null, desvincula todos os temas.
     */
    protected function syncAllThemeAssetLinks(?Theme $activeTheme = null): void
    {
        // Busca todos os temas cadastrados
        $themes = Theme::all();

        foreach ($themes as $theme) {
            // Verifica se este tema específico deve estar vinculado
            $shouldLink = $activeTheme && ($theme->id === $activeTheme->id);

            $this->setThemeAssetLink($theme, $shouldLink);
        }
    }

    /**
     * Executa o comando Artisan de link ou unlink para um tema individual.
     */
    protected function setThemeAssetLink(Theme $theme, bool $enable): void
    {
        // Identificador do tema (slug ou nome)
        $themeIdentifier = Str::lower($theme->slug ?? $theme->name);

        if ($enable) {
            Artisan::call('theme:link', [
                'theme' => $themeIdentifier,
                '--force' => true,
            ]);
        } else {
            Artisan::call('theme:link', [
                'theme' => $themeIdentifier,
                '--unlink' => true,
            ]);
        }
    }

    /**
     * Scan /themes folder and synchronize with the database.
     */
    protected function syncThemes(): void
    {
        $themesPath = base_path('themes');

        if (!File::exists($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);
            return;
        }

        $directories = File::directories($themesPath);
        $scannedFolders = [];

        foreach ($directories as $directory) {
            $folderName = basename($directory);
            $scannedFolders[] = $folderName;
            $manifestPath = $directory . '/theme.json';

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);

                if ($manifest) {
                    Theme::updateOrCreate(
                        ['folder_name' => $folderName],
                        [
                            'name' => $manifest['name'] ?? $folderName,
                            'version' => $manifest['version'] ?? '1.0.0',
                            'description' => $manifest['description'] ?? '',
                            'author' => $manifest['author'] ?? 'Unknown',
                            'screenshot' => $manifest['screenshot'] ?? null,
                        ]
                    );
                }
            }
        }

        // Clean up database records for themes that no longer exist physically on disk
        Theme::whereNotIn('folder_name', $scannedFolders)->delete();
    }

    /**
     * Serve the theme screenshot safely from the internal folder.
     */
    public function screenshot(Theme $theme)
    {
        if ($theme->screenshot) {
            $path = base_path("themes/{$theme->folder_name}/{$theme->screenshot}");

            if (File::exists($path)) {
                return response()->file($path);
            }
        }

        abort(404);
    }
}
