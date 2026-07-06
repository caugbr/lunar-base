<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class SeoResolver
{
    public function resolve(): array
    {
        $route = Route::current();

        if (!$route) {
            return $this->fallback();
        }

        // 1. Lê a entidade que o controlador já resolveu e pendurou na rota
        $entity = $route->parameter('resolved_entity');

        // 2. Formata o SEO de forma limpa e direta
        if ($entity instanceof Page) {
            return $this->formatPageSeo($entity);
        }

        if ($entity instanceof Post) {
            return $this->formatPostSeo($entity);
        }

        if ($entity === 'blog') {
            return $this->fromBlog();
        }

        // 3. Fallback para rotas estáticas nomeadas do core (como 'home', etc.)
        return match ($route->getName()) {
            'home' => $this->fromHome(),
            default => $this->fallback(),
        };
    }

    private function fromHome(): array
    {
        $settings = settingsGroup('general');

        return [
            'title' => $this->stringOr($settings['site_name'], config('app.name')),
            'description' => $this->stringOr($settings['site_description'], ''),
            'image' => $this->settingsImageUrl($settings['site_thumbnail'] ?? null),
            'url' => route('home'),
            'type' => 'website',
        ];
    }

    private function formatPageSeo(Page $page): array
    {
        $image = $this->stringOr(
            $page->thumbnail?->thumb_url,
            $page->thumbnail?->url,
            $this->settingsImageUrl(setting('site_thumbnail'))
        );

        $description = $this->stringOr(
            $page->excerpt,
            setting('site_description')
        );

        return [
            'title' => $page->title . ' | ' . setting('general.site_name', config('app.name')),
            'description' => $description,
            'image' => $image,
            'url' => url()->current(),
            'type' => 'article',
        ];
    }

    private function formatPostSeo(Post $post): array
    {
        $image = $this->stringOr(
            $post->thumbnail?->large_url,
            $post->thumbnail?->url,
            $this->settingsImageUrl(setting('site_thumbnail'))
        );

        return [
            'title' => $post->title . ' | ' . setting('general.site_name', config('app.name')),
            'description' => $this->stringOr($post->excerpt, setting('site_description')),
            'image' => $image,
            'url' => $post->url,
            'type' => 'article',
        ];
    }

    private function fromBlog(): array
    {
        $settings = settingsGroup('general');

        return [
            'title' => 'Blog | ' . $this->stringOr($settings['site_name'], config('app.name')),
            'description' => $this->stringOr($settings['site_description'], ''),
            'image' => $this->settingsImageUrl($settings['site_thumbnail'] ?? null),
            'url' => url()->current(),
            'type' => 'website',
        ];
    }

    private function fallback(): array
    {
        $settings = settingsGroup('general');

        return [
            'title' => $this->stringOr($settings['site_name'], config('app.name')),
            'description' => $this->stringOr($settings['site_description'], ''),
            'image' => $this->settingsImageUrl($settings['site_thumbnail'] ?? null),
            'url' => url()->current(),
            'type' => 'article',
        ];
    }

    private function settingsImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url('media/settings/' . ltrim($path, '/'));
    }

    private function stringOr(?string ...$values): string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return '';
    }
}
