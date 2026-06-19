<?php

namespace App\View\Components;

use App\Services\SeoResolver;
use Illuminate\View\Component;

class SeoMeta extends Component
{
    public array $seo;

    public function __construct(
        private SeoResolver $resolver,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?string $type = null,
    ) {}

    public function render()
    {
        $resolved = $this->resolver->resolve();

        $this->seo = [
            'title' => $this->title ?? $resolved['title'],
            'description' => $this->description ?? $resolved['description'],
            'image' => $this->image ?? $resolved['image'],
            'url' => $resolved['url'],
            'type' => $this->type ?? $resolved['type'],
        ];

        return view('components.seo-meta');
    }
}
