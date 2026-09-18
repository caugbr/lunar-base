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
        abort_unless(auth()->user()->hasPermission(['manage-pages', 'manage-own-pages']), 403);

        $isTrash = $request->get('view') === 'trash';
        $user = auth()->user();

        if ($isTrash) {
            $query = Page::onlyTrashed()->forCurrentUser()->with(['author', 'terms']);
        } else {
            $query = Page::forCurrentUser()->with(['author', 'terms']);
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->filled('namespace')) {
            $query->where('namespace', 'like', '%' . $request->input('namespace') . '%');
        }

        if (!$isTrash && $request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por autor só é aplicado se ele puder gerenciar páginas de outros
        if ($user->hasPermission('manage-pages') && $request->filled('author_id')) {
            $query->where('author_id', $request->input('author_id'));
        }

        $pages = $query->orderBy('created_at', 'desc')
                       ->paginate(setting('reading.pagination_max_items'))
                       ->withQueryString();

        $counts = [
            'all'       => Page::forCurrentUser()->count(),
            'published' => Page::forCurrentUser()->where('status', 'published')->count(),
            'draft'     => Page::forCurrentUser()->where('status', 'draft')->count(),
            'trash'     => Page::onlyTrashed()->forCurrentUser()->count(),
        ];

        $namespaces = $this->getNamespaces();

        $authors = $user->hasPermission('manage-pages')
            ? User::whereIn('role', ['admin', 'editor', 'author'])->orderBy('name')->get()
            : collect([$user]);

        return view('admin.pages.index', compact('pages', 'namespaces', 'authors', 'counts', 'isTrash'));
    }

    public function destroy(Page $page)
    {
        abort_unless($page->canBeManagedBy(), 403, 'Você não tem permissão para excluir esta página.');

        $page->delete();

        log_admin("Página movida para a lixeira: {$page->title}", "pages");

        return redirect()->route('admin.pages.index')
            ->with('success', 'Página movida para a lixeira com sucesso!');
    }

    public function restore($id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        abort_unless($page->canBeManagedBy(), 403, 'Você não tem permissão para restaurar esta página.');

        $page->restore();

        log_admin("Página restaurada da lixeira: {$page->title}", "pages");

        return redirect()->back()
            ->with('success', 'Página restaurada com sucesso!');
    }

    public function purge($id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        abort_unless($page->canBeManagedBy(), 403, 'Você não tem permissão para excluir definitivamente esta página.');

        $title = $page->title;

        $page->terms()->detach();
        $page->forceDelete();

        log_admin("Página excluída definitivamente: {$title}", "pages");

        return redirect()->back()
            ->with('success', 'Página excluída definitivamente!');
    }

    public function emptyTrash()
    {
        abort_unless(auth()->user()->hasPermission(['manage-pages', 'manage-own-pages']), 403);

        $trashed = Page::onlyTrashed()->forCurrentUser()->get();

        foreach ($trashed as $page) {
            $page->terms()->detach();
            $page->forceDelete();
        }

        log_admin("Lixeira de páginas esvaziada.", "pages");

        return redirect()->route('admin.pages.index', ['view' => 'trash'])
            ->with('success', 'Lixeira esvaziada com sucesso!');
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission(['manage-pages', 'manage-own-pages']), 403);

        $user = auth()->user();

        $users = $user->hasPermission('manage-pages')
            ? User::whereIn('role', ['admin', 'editor', 'author'])->orderBy('name')->get()
            : collect([$user]);

        $currentUserId = $user->id;
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
        abort_unless(auth()->user()->hasPermission(['manage-pages', 'manage-own-pages']), 403);

        $user = auth()->user();

        // Se for autor restrito, força o author_id para o próprio usuário
        if (!$user->hasPermission('manage-pages')) {
            $request->merge(['author_id' => $user->id]);
        }

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
            'content' => 'nullable|string',
            'content_json' => 'nullable',
            'excerpt' => 'nullable|string',
            'namespace' => 'nullable|string',
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
        abort_unless($page->canBeManagedBy(), 403, 'Você não tem permissão para editar esta página.');

        $user = auth()->user();

        $users = $user->hasPermission('manage-pages')
            ? User::whereIn('role', ['admin', 'editor', 'author'])->orderBy('name')->get()
            : collect([$page->author ?? $user]);

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
        abort_unless($page->canBeManagedBy(), 403, 'Você não tem permissão para editar esta página.');

        $user = auth()->user();

        // Se for autor restrito, impede a alteração do author_id
        if (!$user->hasPermission('manage-pages')) {
            $request->merge(['author_id' => $user->id]);
        }

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
            'content' => 'nullable|string',
            'content_json' => 'nullable',
            'excerpt' => 'nullable|string',
            'namespace' => 'nullable|string',
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

    /**
     * Retorna todos os namespaces únicos (das páginas e dos domínios configurados).
     * Sinaliza os namespaces que pertencem a domínios ativos.
     *
     * @return array Array associativo no formato ['valor' => 'Rótulo']
     */
    public function getNamespaces(): array
    {
        // Pega os namespaces mapeados em Domínios no Settings (via helper siteDomains())
        $domainNamespaces = collect(siteDomains())
            ->pluck('namespace')
            ->filter()
            ->map(fn($ns) => trim($ns))
            ->unique()
            ->values()
            ->toArray();

        // Pega os namespaces já em uso fisicamente na tabela pages
        $dbNamespaces = Page::select('namespace')
            ->distinct()
            ->whereNotNull('namespace')
            ->where('namespace', '!=', '')
            ->pluck('namespace')
            ->map(fn($ns) => trim($ns))
            ->toArray();

        // Unifica e ordena todos os namespaces sem duplicados
        $allNamespaces = collect(array_merge($domainNamespaces, $dbNamespaces))
            ->unique()
            ->sort()
            ->values();

        // Monta o array associativo ['valor' => 'Rótulo']
        $options = [];
        foreach ($allNamespaces as $ns) {
            $isDomain = in_array($ns, $domainNamespaces, true);

            // Adiciona um sinalizador visual se for um namespace de domínio ativo
            $label = $isDomain ? "{$ns} (Domínio)" : $ns;

            $options[$ns] = $label;
        }

        return $options;
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
