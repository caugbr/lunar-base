<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::with(['author', 'terms']);

        // Filtro por título
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        // Filtro por namespace
        if ($request->filled('namespace')) {
            $query->where('namespace', 'like', '%' . $request->input('namespace') . '%');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por autor
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->input('author_id'));
        }

        $pages = $query->orderBy('created_at', 'desc')->paginate(setting('reading.pagination_max_items'));
        $namespaces = $this->getNamespaces();

        // Dados para os selects dos filtros
        $authors = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();

        return view('admin.pages.index', compact('pages', 'namespaces', 'authors'));
    }

    public function create()
    {
        $users = User::whereIn('role', ['admin', 'editor'])->orderBy('name')->get();
        $currentUserId = Auth::id();
        $templates = Config::get('pageTemplates.templates', []);
        $taxonomies = Taxonomy::with('terms')->get();
        $namespaces = $this->getNamespaces();

        return view('admin.pages.create', compact('users', 'namespaces', 'currentUserId', 'templates', 'taxonomies'));
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
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('pageTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ]);

        $page = Page::create($validated);

        // Sincronizar termos
        $page->terms()->sync($request->term_ids ?? []);

        // Atualiza a galeria
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

        // Carrega taxonomias e termos para o formulário
        $taxonomies = Taxonomy::with('terms')->get();
        // IDs dos termos já associados à página
        $selectedTermIds = $page->terms->pluck('id')->toArray();

        $namespaces = $this->getNamespaces();

        return view('admin.pages.edit', compact('page', 'users', 'templates', 'taxonomies', 'selectedTermIds', 'namespaces'));
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
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string|in:' . implode(',', array_keys(Config::get('pageTemplates.templates', []))),
            'thumbnail_id' => 'nullable|exists:media,id',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'exists:terms,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ]);

        $page->update($validated);

        // Sincronizar termos
        $page->terms()->sync($request->term_ids ?? []);

        // Atualiza a galeria
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

    /**
     * Retorna todos os namespaces únicos existentes na tabela pages.
     * Inclui null/vazio como uma opção para páginas sem namespace.
     */
    public function getNamespaces()
    {
        return Page::select('namespace')
            ->distinct()
            ->whereNotNull('namespace')
            ->where('namespace', '!=', '')
            ->orderBy('namespace')
            ->pluck('namespace');
    }
}
