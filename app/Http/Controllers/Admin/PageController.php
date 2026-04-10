<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Models\Taxonomy;
// use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('author', 'terms')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $users = User::whereIn('role_id', [1, 2])->orderBy('name')->get();
        $currentUserId = Auth::id(); // 🆕 Pega o ID do usuário logado
        $templates = Config::get('pageTemplates.templates', []);
        $taxonomies = Taxonomy::with('terms')->get();

        return view('admin.pages.create', compact('users', 'taxonomies', 'currentUserId', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'author_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|string',
            'term_ids' => 'nullable|array',  // 🆕
            'term_ids.*' => 'exists:terms,id',  // 🆕
        ]);

        $page = Page::create($validated);

        // 🆕 Associar termos à página
        if (!empty($request->term_ids)) {
            $page->terms()->sync($request->term_ids);
        }

        return redirect()->route('admin.pages.index')
            ->with('success', 'Página criada com sucesso!');
    }

    public function edit(Page $page)
    {
        $users = User::whereIn('role_id', [1, 2])->orderBy('name')->get();

        // Carrega taxonomias e termos para o formulário
        $taxonomies = Taxonomy::with('terms')->get();

        // IDs dos termos já associados à página
        $selectedTermIds = $page->terms->pluck('id')->toArray();
        $templates = Config::get('pageTemplates.templates', []);

        return view('admin.pages.edit', compact('page', 'users', 'templates', 'taxonomies', 'selectedTermIds'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'author_id' => 'nullable|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'template' => 'nullable|string',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'exists:terms,id',
        ]);

        // Se author_id foi enviado, busca o nome do usuário
        if (!empty($validated['author_id'])) {
            $user = User::find($validated['author_id']);
            $validated['author'] = $user->name;
        }
        unset($validated['author_id']);

        $page->update($validated);

        // 🆕 Sincronizar termos
        $page->terms()->sync($request->term_ids ?? []);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Página atualizada com sucesso!');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Página removida com sucesso!');
    }
}
