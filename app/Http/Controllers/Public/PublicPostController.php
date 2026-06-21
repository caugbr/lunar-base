<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Term;
use Illuminate\Http\Request;
use App\Helpers\ContentHelper;
use App\Traits\GetSiteElements;

class PublicPostController extends Controller
{
    use GetSiteElements;

    /**
     * Feed/listagem de posts: /blog
     */
    public function index(Request $request)
    {
        $query = Post::with('thumbnail')->published()->feedOrder();

        if ($request->filled('term')) {
            $query->whereHas('terms', fn($q) => $q->where('slug', $request->input('term')));
        }

        $posts = $query->paginate(setting('reading.posts_max_items'));
        $menu = $this->buildMenu();
        $termsAndPrivacy = $this->getTermsAndPrivacyPages();

        return view('public.blog.index', compact('posts', 'menu', 'termsAndPrivacy'));
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

        $menu = $this->buildMenu();
        $termsAndPrivacy = $this->getTermsAndPrivacyPages();

        $reactionData = setting('reading.post_use_reaction', false) ? [
            'positive' => $post->positiveCount(),
            'negative' => $post->negativeCount(),
            'user' => $post->userReaction(),
        ] : [];

        $post->content = ContentHelper::parseShortcodes($post->content);

        return view('public.post-templates.' . $post->template, compact('post', 'menu', 'termsAndPrivacy', 'reactionData'));
    }

    /**
     * Listagem filtrada por taxonomia: /blog/{taxonomy}/{term}
     */
    public function byTerm(string $taxonomySlug, string $termSlug)
    {
        $term = Term::whereHas('taxonomy', fn($q) => $q->where('slug', $taxonomySlug))
            ->where('slug', $termSlug)
            ->firstOrFail();

        $posts = Post::with('thumbnail')
            ->published()
            ->byTerm($taxonomySlug, $termSlug)
            ->feedOrder()
            ->paginate(setting('reading.posts_max_items'));

        $menu = $this->buildMenu();
        $termsAndPrivacy = $this->getTermsAndPrivacyPages();

        return view('public.blog.term', compact('posts', 'term', 'menu', 'termsAndPrivacy'));
    }
}
