<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\Media;
use App\Models\PostMeta;
use App\Helpers\ContentHelper;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(['manage-posts', 'manage-own-posts']), 403);

        $isTrash = $request->get('view') === 'trash';
        $user = auth()->user();

        // Query base já com o escopo do usuário atual aplicado
        $query = $isTrash
            ? Post::onlyTrashed()->forCurrentUser()->with(['author', 'terms'])
            : Post::forCurrentUser()->with(['author', 'terms']);

        // Filtro por título
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        // Filtro por status (apenas se não estiver na lixeira)
        if (!$isTrash && $request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por autor (só aplicado se o usuário tiver permissão para ver outros autores)
        if ($user->hasPermission('manage-posts') && $request->filled('author_id')) {
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
                       ->orderBy('created_at', 'desc')
                       ->paginate(setting('reading.pagination_max_items'))
                       ->withQueryString();

        // Contagens para as abas (respeitam automaticamente o escopo do autor via Model)
        $counts = [
            'all'       => Post::forCurrentUser()->count(),
            'published' => Post::forCurrentUser()->where('status', 'published')->count(),
            'draft'     => Post::forCurrentUser()->where('status', 'draft')->count(),
            'trash'     => Post::onlyTrashed()->forCurrentUser()->count(),
        ];

        // Lista de autores no select do filtro:
        // Se puder gerenciar todos, lista quem tem papéis adequados. Se for apenas autor, lista apenas ele mesmo.
        $authors = $user->hasPermission('manage-posts')
            ? User::whereIn('role', ['admin', 'editor', 'author'])->orderBy('name')->get()
            : collect([$user]);

        return view('admin.posts.index', compact('posts', 'authors', 'counts', 'isTrash'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission(['manage-posts', 'manage-own-posts']), 403);

        $user = auth()->user();

        // Se for autor restrito, só pode atribuir o post a si mesmo
        $users = $user->hasPermission('manage-posts')
            ? User::whereIn('role', ['admin', 'editor', 'author'])->orderBy('name')->get()
            : collect([$user]);

        $currentUserId = $user->id;
        $templates = Config::get('postTemplates.templates', []);
        $taxonomies = Taxonomy::forType('post')->with('terms')->get();
        $existingMetaKeys = PostMeta::select('meta_key')
            ->distinct()
            ->orderBy('meta_key')
            ->pluck('meta_key')
            ->toArray();

        return view('admin.posts.create', compact('users', 'currentUserId', 'templates', 'taxonomies', 'existingMetaKeys'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(['manage-posts', 'manage-own-posts']), 403);

        $user = auth()->user();

        // Se tiver apenas permissão para os próprios posts, força o author_id para o ID dele
        if (!$user->hasPermission('manage-posts')) {
            $request->merge(['author_id' => $user->id]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'content' => 'nullable|string',
            'content_json' => 'nullable',
            'excerpt' => 'nullable|string',
            'author_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('postTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|boolean',
            'sticky' => 'nullable|boolean',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'nullable|exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ]);

        $validated['content'] = ContentHelper::sanitizeForStorage($request->content);

        // Se status published mas sem published_at, define agora
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        $post->terms()->sync(array_filter($validated['term_ids'] ?? []));

        $post->meta()->delete();

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

        // Atualiza a galeria
        if (isset($validated['gallery_ids'])) {
            Media::whereIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => $post->id,
                    'mediaable_type' => Post::class
                ]);
        }

        logAdmin("Post criado: {$validated['title']}", "posts");

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post criado com sucesso!');
    }

    public function edit(Post $post)
    {
        abort_unless($post->canBeManagedBy(), 403, 'Você não tem permissão para editar este post.');

        $user = auth()->user();

        // Se for autor restrito, só vê a si mesmo no select
        $users = $user->hasPermission('manage-posts')
            ? User::whereIn('role', ['admin', 'editor', 'author'])->orderBy('name')->get()
            : collect([$post->author ?? $user]);

        $templates = Config::get('postTemplates.templates', []);
        $taxonomies = Taxonomy::forType('post')->with('terms')->get();
        $selectedTermIds = $post->terms->pluck('id')->toArray();
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
        abort_unless($post->canBeManagedBy(), 403, 'Você não tem permissão para editar este post.');

        $user = auth()->user();

        // Se for autor restrito, garante que não troque o author_id
        if (!$user->hasPermission('manage-posts')) {
            $request->merge(['author_id' => $user->id]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts')->ignore($post->id),
            ],
            'content' => 'nullable|string',
            'content_json' => 'nullable',
            'excerpt' => 'nullable|string',
            'author_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('postTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|boolean',
            'sticky' => 'nullable|boolean',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'nullable|exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ]);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        $post->terms()->sync(array_filter($validated['term_ids'] ?? []));

        $post->meta()->delete();

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

        logAdmin("Post editado: {$validated['title']}", "posts");

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post atualizado com sucesso!');
    }

    public function destroy(Post $post)
    {
        abort_unless($post->canBeManagedBy(), 403, 'Você não tem permissão para excluir este post.');

        $post->delete();

        logAdmin("Post movido para a lixeira: {$post->title}", "posts");

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post movido para a lixeira!');
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        abort_unless($post->canBeManagedBy(), 403, 'Você não tem permissão para restaurar este post.');

        $post->restore();

        logAdmin("Post restaurado da lixeira: {$post->title}", "posts");

        return redirect()->back()
            ->with('success', 'Post restaurado com sucesso!');
    }

    public function purge($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        abort_unless($post->canBeManagedBy(), 403, 'Você não tem permissão para excluir definitivamente este post.');

        $title = $post->title;

        $post->terms()->detach();
        $post->meta()->delete();
        $post->forceDelete();

        logAdmin("Post excluído definitivamente: {$title}", "posts");

        return redirect()->back()
            ->with('success', 'Post excluído definitivamente!');
    }

    public function emptyTrash()
    {
        abort_unless(auth()->user()->hasPermission(['manage-posts', 'manage-own-posts']), 403);

        // O escopo forCurrentUser garante que o autor só esvazie os posts que eram dele!
        $trashed = Post::onlyTrashed()->forCurrentUser()->get();

        foreach ($trashed as $post) {
            $post->terms()->detach();
            $post->meta()->delete();
            $post->forceDelete();
        }

        logAdmin("Lixeira de posts esvaziada.", "posts");

        return redirect()->route('admin.posts.index', ['view' => 'trash'])
            ->with('success', 'Lixeira esvaziada com sucesso!');
    }
}
