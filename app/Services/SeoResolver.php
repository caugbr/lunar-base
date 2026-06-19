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

        return match ($route?->getName()) {
            'home' => $this->fromHome(),
            'public.page', 'public.widget.page' => $this->fromPage($route),
            'public.post' => $this->fromPost($route),
            'public.blog.index', 'public.blog.term' => $this->fromBlog(),
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

    private function fromPage($route): array
    {
        $page = $route->parameter('page')
            ?? $this->resolvePageFromRoute($route);

        if (!$page) {
            return $this->fallback();
        }

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
            'title' => $page->title,
            'description' => $description,
            'image' => $image,
            'url' => url()->current(),
            'type' => 'article',
        ];
    }

    private function fromPost($route): array
    {
        $post = $route->parameter('post');

        if (!$post) {
            return $this->fallback();
        }

        $image = $this->stringOr(
            $post->thumbnail?->thumb_url,
            $post->thumbnail?->url,
            $this->settingsImageUrl(setting('site_thumbnail'))
        );

        return [
            'title' => $post->title,
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

    private function resolvePageFromRoute($route): ?Page
    {
        $slug = $route->parameter('slug');
        $widgetSlug = $route->parameter('widget_slug');

        if ($widgetSlug) {
            return Page::where('slug', $slug)
                ->whereHas('widget', fn($q) => $q->where('slug', $widgetSlug))
                ->first();
        }

        return Page::where('slug', $slug)->first();
    }
}
