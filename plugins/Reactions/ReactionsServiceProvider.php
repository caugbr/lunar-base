<?php

namespace Plugins\Reactions;

use Illuminate\Support\ServiceProvider;
use App\Support\Settings;
use App\Support\HookManager;
use Plugins\Reactions\Helpers\ReactionsHelper;

class ReactionsServiceProvider extends ServiceProvider
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
        // Carrega as views do plugin
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'reactions');

        // Injeta as configurações do painel dinamicamente
        $this->registerSettings();

        // Registra o componente no seu novo hook 'post.meta_end'
        HookManager::register('post.meta_end', function($params) {
            $post = $params['post'] ?? null;
            if (!$post) return '';

            if (!setting('reading.post_use_reaction', false)) {
                return '';
            }

            // Lê as informações diretamente através do helper sem necessitar de Traits no Model
            $reactionData = [
                'positive' => ReactionsHelper::positiveCount($post),
                'negative' => ReactionsHelper::negativeCount($post),
                'user' => ReactionsHelper::userReaction($post),
            ];

            if (view()->exists('reactions::links')) {
                return view('reactions::links', [
                    'type' => 'post',
                    'id' => $post->id,
                    'data' => $reactionData
                ])->render();
            }

            return '';
        }, 'Reactions Plugin');
    }

    protected function registerSettings(): void
    {
        Settings::add([
            'type' => 'subtitle',
            'label' => 'Plugin: Reactions',
        ], 'reading');

        Settings::add([
            'key' => 'post_use_reaction',
            'type' => 'switch',
            'active' => 'Sim',
            'inactive' => 'Não',
            'label' => 'Usar reações nos posts',
            'description' => 'Habilita reações nos posts (Like)',
            'default' => true,
        ], 'reading');

        Settings::add([
            'key' => 'post_reaction_type',
            'type' => 'select',
            'options' => [
                'thumbs' => 'Legal',
                'heart' => 'Coração',
                'star' => 'Estrela',
            ],
            'label' => 'Tipo de reação',
            'description' => 'Se as reações estão habilitadas nos posts, que tipo usar?',
            'default' => 'thumbs',
            'depends_on' => [
                'field' => 'post_use_reaction',
                'operator' => '===',
                'value' => true,
            ],
        ], 'reading');

        Settings::add([
            'key' => 'post_negative_reaction',
            'type' => 'switch',
            'active' => 'Positivo e negativo',
            'inactive' => 'Apenas positivo',
            'label' => 'Usar reação negativa',
            'description' => 'Habilita reações negativas nos posts (Dislike)',
            'default' => false,
            'depends_on' => [
                'field' => 'post_use_reaction',
                'operator' => '===',
                'value' => true,
            ],
        ], 'reading');

        Settings::add([
            'key' => 'unique_reaction',
            'type' => 'switch',
            'active' => 'Uma reação por visitante',
            'inactive' => 'Reações ilimitadas',
            'label' => 'Reação única',
            'description' => 'Se ativado, cada visitante (IP) pode reagir apenas uma vez.',
            'default' => true,
            'depends_on' => [
                'field' => 'post_use_reaction',
                'operator' => '===',
                'value' => true,
            ],
        ], 'reading');
    }
}
