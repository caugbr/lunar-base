<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Term;
use Illuminate\Http\Request;
use App\Helpers\ContentHelper;

class PublicPostController extends Controller
{
    /**
     * Feed/listagem de posts: /blog
     */
    public function index(Request $request)
    {
        // 💡 Identifica que é a listagem do blog
        request()->route()->setParameter('resolved_entity', 'blog');

        $query = Post::with('thumbnail')->published()->feedOrder();

        if ($request->filled('term')) {
            $query->whereHas('terms', fn($q) => $q->where('slug', $request->input('term')));
        }

        $posts = $query->paginate(setting('reading.posts_max_items'));

        return view('public.blog.index', compact('posts'));
    }

    /**
     * Post individual: /blog/{slug}
     */
    public function show(string $slug)
    {
        $post = Post::with(['thumbnail', 'terms.taxonomy'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // 💡 Injeta o modelo de post diretamente na rota ativa
        request()->route()->setParameter('resolved_entity', $post);

        $post->content = ContentHelper::parseShortcodes($post->content);

        return view('public.post-templates.' . $post->template, compact('post'));
    }

    /**
     * Listagem filtrada por taxonomia: /blog/{taxonomy}/{term}
     */
    public function byTerm(string $taxonomySlug, string $termSlug)
    {
        // Identifica que é uma listagem de blog por termo
        request()->route()->setParameter('resolved_entity', 'blog');

        $term = Term::whereHas('taxonomy', fn($q) => $q->where('slug', $taxonomySlug))
            ->where('slug', $termSlug)
            ->firstOrFail();

        $posts = Post::with('thumbnail')
            ->published()
            ->byTerm($taxonomySlug, $termSlug)
            ->feedOrder()
            ->paginate(setting('reading.posts_max_items'));

        return view('public.blog.term', compact('posts', 'term'));
    }
}
