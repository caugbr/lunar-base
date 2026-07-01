<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Theme;
use Illuminate\Support\Facades\File;

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
        // Deactivate all other themes in a single database query
        Theme::where('id', '!=', $theme->id)->update(['is_active' => false]);

        // Activate the selected theme
        $theme->update(['is_active' => true]);

        return back()->with('success', "Theme '{$theme->name}' has been activated!");
    }

    public function toggle(Theme $theme)
    {
        // Se o tema já estiver ativo, apenas desativamos ele
        if ($theme->is_active) {
            $theme->update(['is_active' => false]);
            return back()->with('success', "Tema '{$theme->name}' foi desativado.");
        }

        // Se não estiver ativo, desativamos TODOS os outros e ativamos este
        Theme::query()->update(['is_active' => false]);
        $theme->update(['is_active' => true]);

        return back()->with('success', "Tema '{$theme->name}' foi ativado!");
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
