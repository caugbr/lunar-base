<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\Media;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::with(['author', 'terms']);

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->filled('namespace')) {
            $query->where('namespace', 'like', '%' . $request->input('namespace') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->input('author_id'));
        }

        $pages = $query->orderBy('created_at', 'desc')->paginate(setting('reading.pagination_max_items'));
        $namespaces = $this->getNamespaces();
        $authors = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();

        return view('admin.pages.index', compact('pages', 'namespaces', 'authors'));
    }

    public function create()
    {
        $users = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();
        $currentUserId = Auth::id();
        $templates = Config::get('pageTemplates.templates', []);
        $taxonomies = Taxonomy::forType('page')->with('terms')->get();
        $namespaces = $this->getNamespaces();

        $existingMetaKeys = Page::whereNotNull('meta')
            ->pluck('meta')
            ->flatMap(function ($meta) {
                $array = is_array($meta) ? $meta : json_decode($meta, true);
                return array_keys($array ?? []);
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return view('admin.pages.create', compact('users', 'namespaces', 'currentUserId', 'templates', 'taxonomies', 'existingMetaKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) use ($request) {
                    return $query->where('namespace', $request->namespace);
                }),
            ],
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'namespace' => 'nullable|string',
            'is_main' => 'nullable|boolean',
            'author_id' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:pages,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('pageTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'nullable|exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
            // Validação dos metas
            'meta' => 'nullable|array',
            'meta.*.key' => 'nullable|string',
            'meta.*.value' => 'nullable|string',
        ]);

        $validated['content'] = ContentHelper::sanitizeForStorage($request->content);

        // Formata os campos meta para array associativo ['chave' => 'valor']
        $validated['meta'] = $this->formatMetaInput($request->input('meta'));

        $page = Page::create($validated);

        $page->terms()->sync(array_filter($validated['term_ids'] ?? []));

        if (isset($validated['gallery_ids'])) {
            Media::whereIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => $page->id,
                    'mediaable_type' => Page::class
                ]);
        }

        log_admin("Página criada: {$validated['title']}", "pages");

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Página criada com sucesso!');
    }

    public function edit(Page $page)
    {
        $users = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();
        $templates = Config::get('pageTemplates.templates', []);
        $taxonomies = Taxonomy::forType('page')->with('terms')->get();
        $selectedTermIds = $page->terms->pluck('id')->toArray();
        $namespaces = $this->getNamespaces();

        $existingMetaKeys = Page::whereNotNull('meta')
            ->pluck('meta')
            ->flatMap(function ($meta) {
                $array = is_array($meta) ? $meta : json_decode($meta, true);
                return array_keys($array ?? []);
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return view('admin.pages.edit', compact('page', 'users', 'templates', 'taxonomies', 'selectedTermIds', 'namespaces', 'existingMetaKeys'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) use ($request) {
                    return $query->where('namespace', $request->namespace);
                })->ignore($page->id),
            ],
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'namespace' => 'nullable|string',
            'is_main' => 'nullable|boolean',
            'author_id' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:pages,id|not_in:' . $page->id,
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('pageTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'nullable|exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
            // Validação dos metas
            'meta' => 'nullable|array',
            'meta.*.key' => 'nullable|string',
            'meta.*.value' => 'nullable|string',
        ]);

        $validated['content'] = ContentHelper::sanitizeForStorage($request->content);

        // Formata os campos meta para array associativo ['chave' => 'valor']
        $validated['meta'] = $this->formatMetaInput($request->input('meta'));

        $page->update($validated);

        $page->terms()->sync(array_filter($validated['term_ids'] ?? []));

        if (isset($validated['gallery_ids'])) {
            Media::where('mediaable_id', $page->id)
                ->where('mediaable_type', Page::class)
                ->whereNotIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => null,
                    'mediaable_type' => null
                ]);

            Media::whereIn('id', $validated['gallery_ids'])
                ->update([
                    'mediaable_id' => $page->id,
                    'mediaable_type' => Page::class
                ]);
        }

        log_admin("Página editada: {$validated['title']}", "pages");

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Página atualizada com sucesso!');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        log_admin("Página criada: {$page->title}", "pages");

        return redirect()->route('admin.pages.index')
            ->with('success', 'Página removida com sucesso!');
    }

    public function getNamespaces()
    {
        return Page::select('namespace')
            ->distinct()
            ->whereNotNull('namespace')
            ->where('namespace', '!=', '')
            ->orderBy('namespace')
            ->pluck('namespace');
    }

    /**
     * Converte os pares do formulário [['key' => 'a', 'value' => 'b']]
     * em um array associativo limpo ['a' => 'b'] para o campo JSON do banco.
     */
    protected function formatMetaInput(?array $metaInput): array
    {
        if (empty($metaInput)) {
            return [];
        }

        $formatted = [];
        foreach ($metaInput as $item) {
            $key = trim($item['key'] ?? '');
            $value = $item['value'] ?? '';

            if ($key !== '') {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }
}
