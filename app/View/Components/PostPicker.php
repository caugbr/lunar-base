<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Post;

class PostPicker extends Component
{
    public Collection $posts;
    public string $placeholder;
    public ?string $selected;
    public ?int $exclude;
    public string $valueField;
    public string $labelField;
    public bool $showBadges;

    public function __construct(
        string $placeholder = '-- Selecione um post --',
        ?string $selected = null,
        ?int $exclude = null,
        string $status = 'all',
        string $valueField = 'id',
        string $labelField = 'title',
        bool $showBadges = true
    ) {
        $this->placeholder = $placeholder;
        $this->selected = $selected;
        $this->exclude = $exclude;
        $this->valueField = $valueField;
        $this->labelField = $labelField;
        $this->showBadges = $showBadges;
        $this->posts = $this->loadPosts($status);
    }

    protected function loadPosts(string $status): Collection
    {
        $query = Post::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($this->exclude)) {
            $query->where('id', '!=', $this->exclude);
        }

        return $query->orderBy('sticky', 'desc')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'slug', 'status', 'sticky', 'featured', 'published_at']);
    }

    public function render(): View|Closure|string
    {
        return view('components.post-picker');
    }
}
