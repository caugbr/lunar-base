<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    public function index()
    {
        $taxonomies = Taxonomy::orderBy('created_at', 'desc')->paginate(setting('reading.pagination_max_items'));
        return view('admin.taxonomies.index', compact('taxonomies'));
    }

    public function create()
    {
        return view('admin.taxonomies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:taxonomies',
            'description' => 'nullable|string',
            'hierarchical' => 'boolean',
        ]);

        $validated['hierarchical'] = $request->boolean('hierarchical');

        Taxonomy::create($validated);

        return redirect()->route('admin.taxonomies.index')
            ->with('success', 'Taxonomia criada com sucesso!');
    }

    public function edit(Taxonomy $taxonomy)
    {
        return view('admin.taxonomies.edit', compact('taxonomy'));
    }

    public function update(Request $request, Taxonomy $taxonomy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:taxonomies,slug,' . $taxonomy->id,
            'description' => 'nullable|string',
            'hierarchical' => 'boolean',
        ]);

        $validated['hierarchical'] = $request->boolean('hierarchical');

        $taxonomy->update($validated);

        return redirect()->route('admin.taxonomies.index')
            ->with('success', 'Taxonomia atualizada com sucesso!');
    }

    public function destroy(Taxonomy $taxonomy)
    {
        // Verifica se há termos associados
        if ($taxonomy->terms()->count() > 0) {
            return redirect()->route('admin.taxonomies.index')
                ->with('error', 'Não é possível excluir uma taxonomia que possui termos. Exclua os termos primeiro.');
        }

        $taxonomy->delete();

        return redirect()->route('admin.taxonomies.index')
            ->with('success', 'Taxonomia removida com sucesso!');
    }
}
