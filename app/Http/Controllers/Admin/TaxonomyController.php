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
        $taxonomy = new Taxonomy();
        return view('admin.taxonomies.create', compact('taxonomy'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'required|string|max:255|unique:taxonomies',
            'description'    => 'nullable|string',
            'hierarchical'   => 'boolean',
            'unique'         => 'boolean',
            'target_types'   => 'nullable|array',
            'target_types.*' => 'string',
        ]);

        $validated['hierarchical'] = $request->boolean('hierarchical');
        $validated['unique']       = $request->boolean('unique');

        // Se nenhum checkbox for marcado, salva null (torna a taxonomia global)
        $validated['target_types'] = $request->filled('target_types')
            ? array_values($request->input('target_types'))
            : null;

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
            'name'           => 'required|string|max:255',
            'slug'           => 'required|string|max:255|unique:taxonomies,slug,' . $taxonomy->id,
            'description'    => 'nullable|string',
            'hierarchical'   => 'boolean',
            'unique'         => 'boolean',
            'target_types'   => 'nullable|array',
            'target_types.*' => 'string',
        ]);

        $validated['hierarchical'] = $request->boolean('hierarchical');
        $validated['unique']       = $request->boolean('unique');

        // Se desmarcar todos os tipos, volta a ser global (null)
        $validated['target_types'] = $request->filled('target_types')
            ? array_values($request->input('target_types'))
            : null;

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
