<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Models\Theme;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $activeTheme = Theme::where('is_active', true)->first();

        if (!$activeTheme) {
            return;
        }

        $this->registerTheme($activeTheme);
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
