<?php

namespace Plugins\Forms;

use Illuminate\Support\ServiceProvider;
// use App\Support\HookManager;
use App\Helpers\ContentHelper;
// use App\Support\DynamicRoutes;
use Plugins\Forms\Models\Form;
// use Plugins\Forms\Models\FormSubmission;
// use Plugins\Forms\Http\Controllers\FormsController;
// use Plugins\Forms\Http\Controllers\FormSubmissionController;

class FormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $routesFile = __DIR__ . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'forms');

        ContentHelper::registerShortcode(
            'form',
            function($attributes, $content) {
                $slug = $attributes['slug'] ?? null;
                if (!$slug) return '';

                $form = Form::active()->where('slug', $slug)->first();
                return $form ? view('forms::public.embed', ['form' => $form])->render() : '';
            },
            'Renderiza um formulário',
            '[form slug="form1"]',
            [
                'slug' =>[
                    'label'       => 'Slug registrado para o formulário',
                    'type'        => 'text',
                    'placeholder' => 'Slug do formulário',
                ],
            ]
        );

        \App\Support\AdminMenu::add([
            'label' => 'Formulários',
            'icon'  => 'form',
            'route' => 'admin.forms.index',
            'active' => 'admin.forms.*',
            'permission' => 'manage-pages',
        ], 'Taxonomias');

        \App\Support\AdminMenu::addSubItem('Formulários', [
            'label' => 'Novo Formulário',
            'icon'  => 'form',
            'route' => 'admin.forms.create',
            'active' => 'admin.forms.create',
            'role' => 'manage-pages',
        ]);
    }
}
