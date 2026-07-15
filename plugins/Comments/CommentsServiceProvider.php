<?php

namespace Plugins\Comments;

use Illuminate\Support\ServiceProvider;
use App\Models\Post;
use Plugins\Comments\Models\Comment;

class CommentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $routesFile = __DIR__ . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject the polymorphic relationship on the fly into the Post model
        Post::resolveRelationUsing('comments', function ($post) {
            return $post->morphMany(Comment::class, 'commentable')
                ->whereNull('parent_id')
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc');
        });

        // Load plugin resources
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'comments');

        // Inject admin menu sub-item under Posts
        \App\Support\AdminMenu::addSubItem('Posts', [
            'label'  => 'Comentários',
            'icon'   => 'message-square',
            'route'  => 'admin.comments.index',
            'active' => 'admin.comments.*',
        ]);

        // Inject settings group and moderation field
        \App\Support\Settings::addGroup('comments', [
            'tab'         => 'Comentários',
            'title'       => 'Comentários',
            'description' => 'Configurações do sistema de comentários',
            'icon'        => 'message-square',
        ]);

        \App\Support\Settings::add([
            'key'         => 'comments_require_moderation',
            'type'        => 'switch',
            'label'       => 'Moderação obrigatória',
            'description' => 'Todos os comentários novos precisam ser aprovados manualmente antes de aparecerem no site.',
            'default'     => false,
            'active'      => 'Moderação ativa',
            'inactive'    => 'Sem moderação',
        ], 'comments');

        \App\Support\Settings::add([
            'key'         => 'pagination_items',
            'type'        => 'number',
            'label'       => 'Itens por página (moderação)',
            'description' => 'Quantidade de comentários exibidos por página na tela de moderação.',
            'default'     => 20,
            'attributes'  => ['min' => 5, 'max' => 100, 'step' => 5],
        ], 'comments');

        \App\Support\HookManager::register('post.footer_end', function($params) {
            $post = $params['post'];

            // Verifica se a view existe antes de renderizar
            if (view()->exists('comments::comments-area')) {
                return view('comments::comments-area', ['model' => $post])->render();
            }

            return '';
        }, 'Comments plugin');
    }
}
