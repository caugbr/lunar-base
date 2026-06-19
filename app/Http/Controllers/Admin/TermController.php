<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index(Request $request)
    {
        $taxonomyId = $request->input('taxonomy_id');

        $query = Term::with('taxonomy');

        if ($taxonomyId) {
            $query->where('taxonomy_id', $taxonomyId);
        }

        $terms = $query->orderBy('taxonomy_id')->orderBy('order')->paginate(20);

        $taxonomies = Taxonomy::orderBy('name')->get();

        return view('admin.terms.index', compact('terms', 'taxonomies', 'taxonomyId'));
    }

    public function create(Request $request)
    {
        $taxonomyId = $request->get('taxonomy_id');

        $taxonomies = Taxonomy::orderBy('name')->get();
        $selectedTaxonomy = $taxonomyId ? Taxonomy::find($taxonomyId) : null;

        // Para termos hierárquicos, carrega os termos pai da taxonomia selecionada
        $parentTerms = collect();
        if ($selectedTaxonomy && $selectedTaxonomy->hierarchical) {
            $parentTerms = Term::where('taxonomy_id', $selectedTaxonomy->id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();
        }

        return view('admin.terms.create', compact('taxonomies', 'selectedTaxonomy', 'parentTerms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:terms',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:terms,id',
            'order' => 'nullable|integer',
        ]);

        $validated['order'] = $validated['order'] ?? 0;

        Term::create($validated);

        return redirect()->route('admin.terms.index', ['taxonomy_id' => $validated['taxonomy_id']])
            ->with('success', 'Termo criado com sucesso!');
    }

    public function edit(Term $term)
    {
        $taxonomies = Taxonomy::orderBy('name')->get();

        // Para termos hierárquicos, carrega os termos pai da mesma taxonomia
        $parentTerms = collect();
        if ($term->taxonomy && $term->taxonomy->hierarchical) {
            $parentTerms = Term::where('taxonomy_id', $term->taxonomy_id)
                ->whereNull('parent_id')
                ->where('id', '!=', $term->id)
                ->orderBy('name')
                ->get();
        }

        return view('admin.terms.edit', compact('term', 'taxonomies', 'parentTerms'));
    }

    public function update(Request $request, Term $term)
    {
        $validated = $request->validate([
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:terms,slug,' . $term->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:terms,id',
            'order' => 'nullable|integer',
        ]);

        $validated['order'] = $validated['order'] ?? 0;

        // Impede que um termo seja pai de si mesmo
        if ($validated['parent_id'] == $term->id) {
            return back()->withErrors(['parent_id' => 'Um termo não pode ser pai de si mesmo.']);
        }

        $term->update($validated);

        return redirect()->route('admin.terms.index', ['taxonomy_id' => $validated['taxonomy_id']])
            ->with('success', 'Termo atualizado com sucesso!');
    }

    public function destroy(Term $term)
    {
        $taxonomyId = $term->taxonomy_id;

        // Verifica se o termo está em uso em qualquer entidade (páginas, posts, etc.)
        if ($term->isInUse()) {
            return redirect()->route('admin.terms.index', ['taxonomy_id' => $taxonomyId])
                ->with('error', 'Não é possível excluir um termo que está em uso.');
        }

        // Verifica se há termos filhos
        if ($term->children()->count() > 0) {
            return redirect()->route('admin.terms.index', ['taxonomy_id' => $taxonomyId])
                ->with('error', 'Não é possível excluir um termo que possui sub-termos. Exclua os sub-termos primeiro.');
        }

        $term->delete();

        return redirect()->route('admin.terms.index', ['taxonomy_id' => $taxonomyId])
            ->with('success', 'Termo removido com sucesso!');
    }
}
