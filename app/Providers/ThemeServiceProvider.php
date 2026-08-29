<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Models\Theme;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\LinkThemeAssets;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            LinkThemeAssets::class,
        ]);
    }
    public function boot(): void
    {
        if (!dbAvailable('themes')) return;

        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $themeToLoad = null;

        // FEATURE: Theme Preview via GET (?preview_theme=pasta_ou_id)
        $isIframe = request()->header('Sec-Fetch-Dest') === 'iframe'
                && str_contains(request()->header('referer') ?? '', '/admin/themes');

        $previewParam = request()->get('preview_theme') ?: session('preview_theme');

        // Só permite o preview se vier o parâmetro GET E for dentro do iframe / admin
        if ($previewParam && $isIframe) {
            $previewTheme = Theme::where('folder_name', $previewParam)
                ->orWhere('id', $previewParam)
                ->first();

            if ($previewTheme) {
                session(['preview_theme' => $previewTheme->folder_name]);
                $themeToLoad = $previewTheme;

                // Cria o link simbólico
                try {
                    \Illuminate\Support\Facades\Artisan::call('theme:link', [
                        'theme' => $previewTheme->folder_name,
                        '--force' => true
                    ]);
                } catch (\Throwable $e) {}
            }
        }

        // Fallback: Tema ativo padrão no banco de dados
        if (!$themeToLoad) {
            $themeToLoad = Theme::where('is_active', true)->first();
        }

        if (!$themeToLoad) {
            return;
        }

        $this->registerTheme($themeToLoad);
    }

    private function registerTheme(Theme $theme): void
    {
        $providerClass = "Themes\\{$theme->folder_name}\\ThemeServiceProvider";

        if (class_exists($providerClass)) {
            $this->app->register($providerClass);
        }

        $this->registerThemeViews($theme);
    }

    private function registerThemeViews(Theme $theme): void
    {
        $themeViews = base_path("themes/{$theme->folder_name}/resources/views");

        if (File::exists($themeViews)) {
            View::prependLocation($themeViews);
        }
    }
}
