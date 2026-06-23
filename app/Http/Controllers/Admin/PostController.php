<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\Media;
use App\Models\PostMeta;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['author', 'terms']);

        // Filtro por título
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por autor
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->input('author_id'));
        }

        // Filtro por destaque
        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        // Filtro por fixado
        if ($request->filled('sticky')) {
            $query->where('sticky', $request->boolean('sticky'));
        }

        $posts = $query->orderBy('sticky', 'desc')
                       ->orderBy('published_at', 'desc')
                       ->paginate(setting('reading.pagination_max_items'));

        // Dados para os selects dos filtros
        $authors = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'authors'));
    }

    public function create()
    {
        $users = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();
        $currentUserId = Auth::id();
        $templates = Config::get('postTemplates.templates', []);
        $taxonomies = Taxonomy::with('terms')->get();
        $existingMetaKeys = PostMeta::select('meta_key')
            ->distinct()
            ->orderBy('meta_key')
            ->pluck('meta_key')
            ->toArray();

        return view('admin.posts.create', compact('users', 'currentUserId', 'templates', 'taxonomies', 'existingMetaKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'author_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('postTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|boolean',
            'sticky' => 'nullable|boolean',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ]);

         $validated['content'] = ContentHelper::sanitizeForStorage($request->content);

        // Se status published mas sem published_at, define agora
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        $post->meta()->delete(); // Limpa tudo e reinsere (simples)

        if ($request->has('meta') && is_array($request->input('meta'))) {
            foreach ($request->input('meta') as $pair) {
                $key = $pair['key'] ?? null;
                $value = $pair['value'] ?? null;

                if (!empty($key) && $value !== null && $value !== '') {
                    PostMeta::create([
                        'post_id' => $post->id,
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            }
        }

        // Sincronizar termos
        $post->terms()->sync($request->term_ids ?? []);

        // Atualiza a galeria
        if (isset($validated['gallery_ids'])) {
            Media::whereIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => $post->id,
                    'mediaable_type' => Post::class
                ]);
        }

        log_admin("Post criado: {$validated['title']}", "posts");

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post criado com sucesso!');
    }

    public function edit(Post $post)
    {
        $users = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();
        $templates = Config::get('postTemplates.templates', []);

        // Carrega taxonomias e termos para o formulário
        $taxonomies = Taxonomy::with('terms')->get();
        // IDs dos termos já associados ao post
        $selectedTermIds = $post->terms->pluck('id')->toArray();

        // Carrega metas do post para o formulário
        $postMeta = $post->meta->pluck('meta_value', 'meta_key')->toArray();

        $existingMetaKeys = PostMeta::select('meta_key')
            ->distinct()
            ->orderBy('meta_key')
            ->pluck('meta_key')
            ->toArray();

        return view('admin.posts.edit', compact(
            'post', 'users', 'templates', 'taxonomies',
            'selectedTermIds', 'postMeta', 'existingMetaKeys'
        ));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts')->ignore($post->id),
            ],
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'author_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('postTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|boolean',
            'sticky' => 'nullable|boolean',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ]);

        // Se publicou agora e não tem published_at, define
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        $post->meta()->delete(); // Limpa tudo e reinsere (simples)

        if ($request->has('meta') && is_array($request->input('meta'))) {
            foreach ($request->input('meta') as $pair) {
                $key = $pair['key'] ?? null;
                $value = $pair['value'] ?? null;

                if (!empty($key) && $value !== null && $value !== '') {
                    PostMeta::create([
                        'post_id' => $post->id,
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            }
        }

        // Sincronizar termos
        $post->terms()->sync($request->term_ids ?? []);

        // Atualiza a galeria
        if (isset($validated['gallery_ids'])) {
            Media::where('mediaable_id', $post->id)
                ->where('mediaable_type', Post::class)
                ->whereNotIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => null,
                    'mediaable_type' => null
                ]);

            Media::whereIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => $post->id,
                    'mediaable_type' => Post::class
                ]);
        }

        log_admin("Post editado: {$validated['title']}", "posts");

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post atualizado com sucesso!');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        log_admin("Post removido: {$post->title}", "posts");

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post removido com sucesso!');
    }
}
