<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\RenderManager;
use App\Models\Post;
use App\Models\Taxonomy;

class RenderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerBlocks();
    }

    public function registerBlocks(): void
    {
        RenderManager::register('featured_posts', function ($params = []) {
            $amount = setting('reading.max_featured_posts', 5);
            $featuredPosts = Post::published()->featured()->feedOrder()->take($amount)->get();

            if ($featuredPosts->isEmpty()) {
                return '';
            }

            $view = 'components.rendered.public.featured-posts';
            return view($view, compact('featuredPosts'))->render();
        });

        RenderManager::register('taxonomy_fields', function ($params = []) {
            $type = $params['type'] ?? 'post';
            $item = $params['item'] ?? null;

            // Busca as taxonomias cadastradas para este tipo ('post' ou 'page')
            $taxonomies = Taxonomy::forType($type)->with('terms')->get();

            // Pega os IDs dos termos vinculados se for edição (ou array vazio se for criação)
            $selectedTermIds = $item ? $item->terms->pluck('id')->toArray() : [];

            return view('components.rendered.admin.taxonomy-fields', compact('taxonomies', 'selectedTermIds', 'type'))->render();
        });
    }
}
